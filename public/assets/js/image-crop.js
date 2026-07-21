/**
 * Recadrage interactif générique (Cropper.js) pour un champ <input type="file">,
 * même principe que le recadrage de bannière (artist/shop.php) mais factorisé
 * pour être réutilisé ailleurs (visuel de style, ...). Nécessite que la page
 * ait déjà chargé cropper.min.js/css.
 *
 * @param {Object} options
 * @param {string} options.fileInputId
 * @param {string} options.modalId Id d'une <dialog class="auth-modal"> à créer dans le HTML (une <dialog>
 *   native est nécessaire : ce recadrage peut s'ouvrir par-dessus une autre modale déjà ouverte via
 *   showModal(), et seule la "top layer" des <dialog> passe correctement au-dessus d'une autre <dialog>).
 * @param {string} options.imageId Id du <img> à l'intérieur du modal, utilisé par Cropper.js.
 * @param {string} options.confirmId
 * @param {string} options.cancelId
 * @param {number} options.aspectRatio
 * @param {number} options.outputWidth
 * @param {number} options.outputHeight
 * @param {string} [options.previewId] Id d'un <img> d'aperçu à mettre à jour après validation.
 * @returns {{openWithUrl: function(string): void}} openWithUrl permet de recadrer une image déjà en
 *   ligne (ex: visuel déjà uploadé par un artiste) sans avoir à la re-uploader depuis le poste de
 *   l'admin — utile quand la personne qui recadre n'a pas le fichier d'origine sur sa machine.
 */
function setupImageCrop(options) {
    var fileInput = document.getElementById(options.fileInputId);
    var modal = document.getElementById(options.modalId);
    var cropImage = document.getElementById(options.imageId);
    var confirmBtn = document.getElementById(options.confirmId);
    var cancelBtn = document.getElementById(options.cancelId);
    var previewImg = options.previewId ? document.getElementById(options.previewId) : null;
    if (!fileInput || !modal || !cropImage || !confirmBtn || !cancelBtn) return {};

    var cropper = null;
    var objectUrl = null;

    function openCrop(src) {
        cropImage.src = src;
        modal.showModal();

        cropImage.onload = function () {
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: options.aspectRatio,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                cropBoxMovable: false,
                cropBoxResizable: false,
                toggleDragModeOnDblclick: false,
                background: false,
            });
        };
    }

    fileInput.addEventListener('change', function () {
        var file = fileInput.files[0];
        if (!file) return;

        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        openCrop(objectUrl);
    });

    function closeModal() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        modal.close();
    }

    cancelBtn.addEventListener('click', function () {
        fileInput.value = '';
        closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        if (!cropper) return;

        cropper.getCroppedCanvas({ width: options.outputWidth, height: options.outputHeight }).toBlob(function (blob) {
            var croppedFile = new File([blob], 'image.png', { type: 'image/png' });
            var dt = new DataTransfer();
            dt.items.add(croppedFile);
            fileInput.files = dt.files;

            if (previewImg) {
                previewImg.src = URL.createObjectURL(blob);
                previewImg.classList.remove('hidden');
            }

            closeModal();
        }, 'image/png');
    });

    return { openWithUrl: openCrop };
}
