<?php

namespace App\Core;

/**
 * Construction d'une clause WHERE de recherche multi-mots-clés,
 * réutilisée par toutes les recherches du site (voir *::search()/
 * *::adminSearch() des modèles). Chaque mot de la requête doit trouver
 * une correspondance dans au moins une des colonnes fournies (ET entre
 * les mots, OU entre les colonnes) — ex: "toile digital" sur
 * [name, username] matche une ligne où "toile" apparaît quelque part ET
 * "digital" apparaît quelque part, pas forcément dans la même colonne.
 */
class SearchHelper
{
    /**
     * @param string $query Requête brute de l'utilisateur (pas encore découpée).
     * @param array<int, string> $columns Expressions SQL déjà qualifiées (ex: 's.name', 'u.username').
     * @param string $paramPrefix Préfixe des paramètres nommés générés (doit être unique si plusieurs appels dans la même requête).
     * @return array{sql: string, params: array<string, string>} sql vide si $query est vide après découpage.
     */
    public static function buildKeywordWhere(string $query, array $columns, string $paramPrefix = 'kw'): array
    {
        $words = array_values(array_filter(preg_split('/\s+/', trim($query)) ?: []));

        if (empty($words) || empty($columns)) {
            return ['sql' => '', 'params' => []];
        }

        $groups = [];
        $params = [];

        foreach ($words as $wordIndex => $word) {
            $columnMatches = [];
            foreach ($columns as $colIndex => $column) {
                $paramName = "{$paramPrefix}_{$wordIndex}_{$colIndex}";
                $columnMatches[] = "{$column} LIKE :{$paramName}";
                $params[$paramName] = '%' . $word . '%';
            }
            $groups[] = '(' . implode(' OR ', $columnMatches) . ')';
        }

        return ['sql' => implode(' AND ', $groups), 'params' => $params];
    }

    /**
     * Suggestions d'autocomplétion : valeurs distinctes correspondant à
     * $query, cherchées indépendamment dans chaque colonne fournie puis
     * fusionnées (une colonne peut ne rien remonter, une autre si).
     *
     * Chaque colonne peut être une simple chaîne (pas de miniature associée,
     * ex: un titre de commande) ou un tableau
     * ['column' => 's.name', 'avatarColumn' => 'u.avatar'] quand la valeur
     * suggérée est associée à un avatar utilisateur — l'URL finale
     * (préfixe /uploads/avatars/, repli sur default.png) est alors résolue
     * ici, avec la même convention que partout ailleurs sur le site (voir
     * shop/show.php, admin/shops.php...).
     *
     * @param \PDO $pdo
     * @param string $fromSql Clause FROM ... JOIN ... complète (sans WHERE), déjà écrite par l'appelant.
     * @param array<int, string|array{column: string, avatarColumn?: string|null}> $columns
     * @param string $query Requête brute de l'utilisateur.
     * @param int $limit Nombre maximum de suggestions renvoyées au total.
     * @return array<int, array{label: string, image: string|null}> Triées, tronquées à $limit.
     */
    public static function suggest(\PDO $pdo, string $fromSql, array $columns, string $query, int $limit = 8): array
    {
        $query = trim($query);

        if ($query === '' || empty($columns)) {
            return [];
        }

        $limit = max(1, $limit);
        $suggestions = [];

        foreach ($columns as $definition) {
            if (is_string($definition)) {
                $definition = ['column' => $definition];
            }

            $column = $definition['column'];
            $avatarColumn = $definition['avatarColumn'] ?? null;
            $avatarSelect = $avatarColumn !== null ? $avatarColumn : 'NULL';

            $stmt = $pdo->prepare(
                "SELECT DISTINCT {$column} AS val, {$avatarSelect} AS avatar
                 FROM {$fromSql}
                 WHERE {$column} LIKE :q
                 ORDER BY {$column} ASC
                 LIMIT {$limit}"
            );
            $stmt->execute(['q' => '%' . $query . '%']);

            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $label = $row['val'];
                if ($label === null || $label === '' || isset($suggestions[$label])) {
                    continue;
                }

                $suggestions[$label] = [
                    'label' => $label,
                    'image' => $avatarColumn !== null
                        ? '/uploads/avatars/' . (!empty($row['avatar']) ? $row['avatar'] : 'default.png')
                        : null,
                ];
            }
        }

        return array_slice(array_values($suggestions), 0, $limit);
    }

    /**
     * Fusionne plusieurs listes de suggestions (voir suggest()) en dédoublonnant
     * par label — utile quand un contexte interroge plusieurs tables sans
     * FROM commun (ex: signalements : pseudos + noms de boutique séparément).
     *
     * @param array<int, array<int, array{label: string, image: string|null}>> $lists
     * @return array<int, array{label: string, image: string|null}>
     */
    public static function mergeSuggestionLists(array $lists, int $limit): array
    {
        $merged = [];

        foreach ($lists as $list) {
            foreach ($list as $item) {
                if (!isset($merged[$item['label']])) {
                    $merged[$item['label']] = $item;
                }
            }
        }

        return array_slice(array_values($merged), 0, $limit);
    }
}
