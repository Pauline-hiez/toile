tailwind.config = {
    theme: {
        extend: {
            colors: {
                bg: '#FFFBF1',
                primary: {
                    DEFAULT: '#7a9e7e',
                    hover: '#6c8f70',
                    light: '#e8f0ea',
                },
                ink: '#2d2d2d',
                muted: '#6b7280',
                border: '#e5ddd0',
                success: { DEFAULT: '#166534', bg: '#dcfce7' },
                warning: { DEFAULT: '#92400e', bg: '#fef3c7' },
                danger: { DEFAULT: '#991b1b', bg: '#fee2e2' },
                info: { DEFAULT: '#1e40af', bg: '#dbeafe' },
                title: { DEFAULT: '#a89a82', bg: '#f2efe9' },
                footer: '#17331b',
            },
            fontFamily: {
                cursive: ['Playfair Display', 'serif'],
                main: ['Inter', 'sans-serif'],
                title: ['Caveat', 'cursive'],
            },
            borderRadius: {
                sm: '6px',
                md: '10px',
            },
            boxShadow: {
                sm: '0 1px 3px rgba(0, 0, 0, 0.08)',
            },
        },
    },
};
