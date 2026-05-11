/**
 * WebMovies – Sistema de i18n
 *
 * Aplica traduções estáticas via atributos data-i18n / data-i18n-placeholder.
 * O idioma ativo é lido do localStorage (chave "wm-lang"), espelhando movies.js.
 *
 * Uso em HTML:
 *   <span data-i18n="nav.login">Entrar</span>
 *   <input data-i18n-placeholder="search.placeholder" placeholder="Buscar filmes…" />
 */

export const TRANSLATIONS = {
    'pt-BR': {
        /* Navbar */
        'search.placeholder':   'Buscar filmes…',
        'nav.login':            'Entrar',
        'nav.profile':          'Meu Perfil',
        'nav.logout':           'Sair',

        /* Hero */
        'hero.featured':        'EM DESTAQUE',
        'hero.details':         'VER DETALHES',
        'hero.favorite':        'FAVORITAR',
        'hero.votes':           'avaliações',

        /* Secções home */
        'section.fav1':         'MEUS',
        'section.fav2':         'FAVORITOS',
        'section.filter1':      'FILTRAR POR',
        'section.filter2':      'CATEGORIA',
        'section.manage':       'Gerir preferências',
        'section.popular_chip': 'Populares',
        'section.genre_suffix': 'DO DIA',
        'section.popular1':     'RECOMENDAÇÕES',
        'section.popular2':     'DO DIA',

        /* Favoritos */
        'favorites.loading':    'A carregar…',
        'favorites.empty':      'Nenhum favorito ainda. Clique no ❤ de um filme para guardar.',

        /* Cards */
        'movies.empty':         'Nenhum filme encontrado.',
        'card.fav_add_title':   'Favoritar',
        'card.fav_remove':      'Remover favorito',

        /* Toasts */
        'toast.fav_add':        '❤ Adicionado aos favoritos',
        'toast.fav_remove':     'Removido dos favoritos',
        'toast.rating':         '★ Avaliação guardada',

        /* Pesquisa */
        'search.results':       'RESULTADOS PARA',
        'search.no_results':    'SEM RESULTADOS PARA',

        /* Login */
        'login.subtitle':       'Entre para favoritar e receber recomendações personalizadas.',
        'login.email_label':    'E-mail',
        'login.pass_label':     'Senha',
        'login.forgot':         'Esqueci a senha',
        'login.submit':         'Entrar',
        'login.no_account':     'Ainda não tem conta?',
        'login.register_link':  'Cadastre-se',
        'login.back':           'Voltar ao catálogo',

        /* Register */
        'register.subtitle':    'Crie sua conta e descubra filmes personalizados para você.',
        'register.name_label':  'Nome Completo',
        'register.name_ph':     'Seu nome completo',
        'register.email_label': 'E-mail',
        'register.pass_label':  'Senha',
        'register.pass_ph':     'Mín. 8 caracteres, A-Z, a-z, 0-9',
        'register.confirm_label': 'Confirmar Senha',
        'register.confirm_ph':  'Repita a senha',
        'register.submit':      'Criar Conta',
        'register.has_account': 'Já tem uma conta?',
        'register.login_link':  'Entrar',
        'register.back':        'Voltar ao catálogo',

        /* Forget */
        'forget.subtitle':      'Informe seu e-mail e enviaremos as instruções de recuperação.',
        'forget.email_label':   'E-mail cadastrado',
        'forget.submit':        'Enviar Instruções',
        'forget.remembered':    'Lembrou a senha?',
        'forget.login_link':    'Voltar ao login',
        'forget.back':          'Voltar ao catálogo',

        /* Perfil */
        'profile.title':        'Meu Perfil',
        'profile.back':         'Voltar ao catálogo',
        'profile.avatar_title': 'Foto de Perfil',
        'profile.avatar_hint':  'JPG, PNG ou WebP · Máx. 2 MB',
        'profile.info_title':   'Dados Pessoais',
        'profile.name_label':   'Nome',
        'profile.email_label':  'E-mail',
        'profile.pass_label':   'Nova Senha',
        'profile.pass_ph':      'Deixe em branco para não alterar',
        'profile.confirm_label':'Confirmar Nova Senha',
        'profile.confirm_ph':   'Repita a nova senha',
        'profile.save':         'Guardar Alterações',
        'profile.genres_title': 'Géneros Favoritos',
        'profile.genres_hint':  'Selecione os géneros que prefere — serão mostrados em destaque na página inicial.',
        'profile.genres_save':  'Guardar Preferências',
        'profile.logout':       'Sair',
    },

    'en-US': {
        /* Navbar */
        'search.placeholder':   'Search movies…',
        'nav.login':            'Login',
        'nav.profile':          'My Profile',
        'nav.logout':           'Sign Out',

        /* Hero */
        'hero.featured':        'FEATURED',
        'hero.details':         'VIEW DETAILS',
        'hero.favorite':        'FAVORITE',
        'hero.votes':           'ratings',

        /* Home sections */
        'section.fav1':         'MY',
        'section.fav2':         'FAVORITES',
        'section.filter1':      'FILTER BY',
        'section.filter2':      'CATEGORY',
        'section.manage':       'Manage preferences',
        'section.popular_chip': 'Popular',
        'section.genre_suffix': 'NOW',
        'section.popular1':     "TODAY'S",
        'section.popular2':     'PICKS',

        /* Favorites */
        'favorites.loading':    'Loading…',
        'favorites.empty':      'No favorites yet. Click ❤ on a movie to save it.',

        /* Cards */
        'movies.empty':         'No movies found.',
        'card.fav_add_title':   'Favorite',
        'card.fav_remove':      'Remove favorite',

        /* Toasts */
        'toast.fav_add':        '❤ Added to favorites',
        'toast.fav_remove':     'Removed from favorites',
        'toast.rating':         '★ Rating saved',

        /* Search */
        'search.results':       'RESULTS FOR',
        'search.no_results':    'NO RESULTS FOR',

        /* Login */
        'login.subtitle':       'Sign in to save favorites and get personalized picks.',
        'login.email_label':    'Email',
        'login.pass_label':     'Password',
        'login.forgot':         'Forgot password',
        'login.submit':         'Sign In',
        'login.no_account':     "Don't have an account?",
        'login.register_link':  'Sign Up',
        'login.back':           'Back to catalog',

        /* Register */
        'register.subtitle':    'Create your account and discover movies tailored for you.',
        'register.name_label':  'Full Name',
        'register.name_ph':     'Your full name',
        'register.email_label': 'Email',
        'register.pass_label':  'Password',
        'register.pass_ph':     'Min. 8 chars, A-Z, a-z, 0-9',
        'register.confirm_label': 'Confirm Password',
        'register.confirm_ph':  'Repeat your password',
        'register.submit':      'Create Account',
        'register.has_account': 'Already have an account?',
        'register.login_link':  'Sign In',
        'register.back':        'Back to catalog',

        /* Forget */
        'forget.subtitle':      'Enter your email and we will send recovery instructions.',
        'forget.email_label':   'Registered Email',
        'forget.submit':        'Send Instructions',
        'forget.remembered':    'Remembered your password?',
        'forget.login_link':    'Back to login',
        'forget.back':          'Back to catalog',

        /* Profile */
        'profile.title':        'My Profile',
        'profile.back':         'Back to catalog',
        'profile.avatar_title': 'Profile Picture',
        'profile.avatar_hint':  'JPG, PNG or WebP · Max 2 MB',
        'profile.info_title':   'Personal Information',
        'profile.name_label':   'Name',
        'profile.email_label':  'Email',
        'profile.pass_label':   'New Password',
        'profile.pass_ph':      'Leave blank to keep current',
        'profile.confirm_label':'Confirm New Password',
        'profile.confirm_ph':   'Repeat new password',
        'profile.save':         'Save Changes',
        'profile.genres_title': 'Favorite Genres',
        'profile.genres_hint':  'Select your preferred genres — they will be highlighted on the home page.',
        'profile.genres_save':  'Save Preferences',
        'profile.logout':       'Sign Out',
    },
};

/** Idioma atual — lido do localStorage. */
function getLang() {
    return localStorage.getItem('wm-lang') || 'pt-BR';
}

/**
 * Traduz uma chave para o idioma atual.
 * Fallback para pt-BR se a chave não existir no idioma pedido.
 */
export function t(key) {
    const lang = getLang();
    return (TRANSLATIONS[lang] ?? TRANSLATIONS['pt-BR'])[key] ?? key;
}

/**
 * Aplica traduções a todos os elementos marcados com:
 *   data-i18n="chave"             → define textContent
 *   data-i18n-placeholder="chave" → define placeholder
 *
 * Também actualiza o atributo lang do <html>.
 */
export function applyI18n() {
    const lang = getLang();
    document.documentElement.lang = lang;

    document.querySelectorAll('[data-i18n]').forEach(el => {
        // Se o elemento só tem nós de texto (sem filhos element), substitui textContent.
        // Se tiver filhos (ex.: <i> ícone), procura o primeiro nó de texto direto e substitui.
        const hasElementChild = [...el.childNodes].some(n => n.nodeType === Node.ELEMENT_NODE);
        if (!hasElementChild) {
            el.textContent = t(el.dataset.i18n);
        } else {
            // Atualiza apenas o nó de texto final (após os ícones)
            let textNode = [...el.childNodes].find(n => n.nodeType === Node.TEXT_NODE && n.textContent.trim());
            if (textNode) {
                textNode.textContent = t(el.dataset.i18n);
            } else {
                // Fallback: adiciona no fim
                el.appendChild(document.createTextNode(t(el.dataset.i18n)));
            }
        }
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        el.placeholder = t(el.dataset.i18nPlaceholder);
    });
}
