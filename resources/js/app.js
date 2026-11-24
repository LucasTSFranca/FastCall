import './bootstrap';

const themeButtons = document.querySelectorAll('.theme-dot');
const themeStatus = document.getElementById('theme-status');

themeButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        const root = document.documentElement;
        const previousTheme = root.dataset.theme;
        const nextTheme = button.dataset.theme;

        root.dataset.theme = nextTheme;
        themeButtons.forEach((themeButton) => {
            const isSelected = themeButton.dataset.theme === nextTheme;
            themeButton.classList.toggle('active', isSelected);
            themeButton.setAttribute('aria-pressed', String(isSelected));
        });

        try {
            const response = await fetch(root.dataset.themeUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ tema: nextTheme }),
            });

            if (!response.ok) {
                throw new Error(`A atualização do tema falhou com o código ${response.status}.`);
            }

            themeStatus.textContent = 'Aparência atualizada.';
            themeStatus.dataset.state = 'success';
        } catch (error) {
            root.dataset.theme = previousTheme;
            themeButtons.forEach((themeButton) => {
                const isSelected = themeButton.dataset.theme === previousTheme;
                themeButton.classList.toggle('active', isSelected);
                themeButton.setAttribute('aria-pressed', String(isSelected));
            });
            themeStatus.textContent = 'Não foi possível salvar a aparência. Tente novamente.';
            themeStatus.dataset.state = 'error';
            console.error('Não foi possível salvar a preferência de tema.', error);
        }
    });
});
