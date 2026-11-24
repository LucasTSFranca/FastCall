import './bootstrap';

const themeButtons = document.querySelectorAll('.theme-dot');
const themeStatus = document.getElementById('theme-status');
const themeCustomizer = document.getElementById('theme-customizer');
const customThemeForm = document.getElementById('custom-theme-form');
const customThemeInputs = document.querySelectorAll('[data-custom-color]');
const root = document.documentElement;

function selectTheme(theme) {
    root.dataset.theme = theme;
    themeButtons.forEach((button) => {
        const isSelected = button.dataset.theme === theme;
        button.classList.toggle('active', isSelected);
        button.setAttribute('aria-pressed', String(isSelected));
    });
    themeCustomizer.hidden = theme !== 'personalizado';
}

function readCustomColors() {
    return Object.fromEntries([...customThemeInputs].map((input) => [
        input.dataset.customColor,
        input.value,
    ]));
}

function applyCustomColors(colors, buttonText) {
    root.style.setProperty('--custom-background', colors.background);
    root.style.setProperty('--custom-buttons', colors.buttons);
    root.style.setProperty('--custom-text', colors.text);
    root.style.setProperty('--custom-button-label', buttonText);
    const customThemeButton = document.querySelector('.theme-dot[data-theme="personalizado"]');
    if (customThemeButton) {
        customThemeButton.style.setProperty('--theme-swatch', colors.buttons);
        customThemeButton.style.setProperty('--theme-secondary-swatch', colors.background);
    }
}

async function saveTheme(theme, colors = null) {
    const payload = { tema: theme };
    if (colors) {
        payload.cores = colors;
    }

    const response = await fetch(root.dataset.themeUpdateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        const result = await response.json().catch(() => ({}));
        const validationMessage = Object.values(result.errors ?? {}).flat()[0];
        throw new Error(validationMessage ?? `A atualização do tema falhou com o código ${response.status}.`);
    }

    themeStatus.textContent = colors ? 'Cores personalizadas salvas.' : 'Aparência atualizada.';
    themeStatus.dataset.state = 'success';
    return response.json();
}

themeButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        const previousTheme = root.dataset.theme;
        const nextTheme = button.dataset.theme;
        selectTheme(nextTheme);

        try {
            await saveTheme(nextTheme);
        } catch (error) {
            selectTheme(previousTheme);
            themeStatus.textContent = error.message || 'Não foi possível salvar a aparência. Tente novamente.';
            themeStatus.dataset.state = 'error';
            console.error('Não foi possível salvar a preferência de tema.', error);
        }
    });
});

customThemeForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const colors = readCustomColors();

    try {
        const result = await saveTheme('personalizado', colors);
        applyCustomColors(colors, result.button_text);
        selectTheme('personalizado');
    } catch (error) {
        themeStatus.textContent = error.message || 'Não foi possível salvar as cores. Tente novamente.';
        themeStatus.dataset.state = 'error';
        console.error('Não foi possível salvar as cores personalizadas.', error);
    }
});
