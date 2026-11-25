const TRANSLATIONS = {
    pt: {
        loading_site: "Carregando plataforma...",
        loading_profiles: "Carregando perfis...",
        loading_messages: "Carregando mensagens...",
        btn_login: "Entrar",
        btn_register: "Criar Conta",
        btn_messages: "Mensagens",
        btn_logout: "Sair",
        btn_filter: "Filtrar",
        btn_send: "Enviar",
        btn_submit_register: "Finalizar Cadastro",
        btn_contact: "Enviar Mensagem",
        hero_title: "Encontre Profissionais, Empresas e Prestadores de Serviços",
        hero_subtitle: "Conecte-se diretamente e troque mensagens em tempo real com especialistas locais ou remotos.",
        search_placeholder: "Buscar por nome, área de atuação ou endereço...",
        filter_all_types: "Todos os perfis",
        filter_all_modalities: "Todas modalidades",
        role_cliente: "Cliente",
        role_profissional: "Profissional",
        role_empresa: "Empresa",
        role_prestador: "Prestador de Serviços",
        mod_remoto: "Remoto",
        mod_presencial: "Presencial",
        mod_hibrido: "Híbrido",
        label_account_type: "Tipo de Conta",
        label_fullname: "Nome Completo",
        label_company_name: "Nome da Empresa",
        label_email: "E-mail",
        label_password: "Senha",
        label_age: "Idade",
        label_cpf: "CPF",
        label_cnpj: "CNPJ",
        label_phone: "Número de Telefone",
        label_address: "Endereço",
        label_area: "Área de Atuação (sem acentos e sem maiúsculas)",
        placeholder_area: "ex: desenvolvimento de software",
        area_rule_warn: "Acentos e letras maiúsculas são removidos automaticamente.",
        label_modality: "Modalidade de Serviço",
        chat_conversations: "Conversas",
        chat_select: "Selecione um contato",
        chat_placeholder: "Digite sua mensagem...",
        no_profiles: "Nenhum perfil encontrado com esses filtros.",
        login_required_chat: "Faça login ou crie uma conta para enviar mensagens.",
        hello_user: "Olá,"
    },
    en: {
        loading_site: "Loading platform...",
        loading_profiles: "Loading profiles...",
        loading_messages: "Loading messages...",
        btn_login: "Sign In",
        btn_register: "Create Account",
        btn_messages: "Messages",
        btn_logout: "Logout",
        btn_filter: "Filter",
        btn_send: "Send",
        btn_submit_register: "Complete Registration",
        btn_contact: "Send Message",
        hero_title: "Find Professionals, Companies & Service Providers",
        hero_subtitle: "Connect directly and exchange real-time messages with local or remote specialists.",
        search_placeholder: "Search by name, expertise area, or address...",
        filter_all_types: "All profiles",
        filter_all_modalities: "All modalities",
        role_cliente: "Client",
        role_profissional: "Professional",
        role_empresa: "Company",
        role_prestador: "Service Provider",
        mod_remoto: "Remote",
        mod_presencial: "On-site",
        mod_hibrido: "Hybrid",
        label_account_type: "Account Type",
        label_fullname: "Full Name",
        label_company_name: "Company Name",
        label_email: "Email",
        label_password: "Password",
        label_age: "Age",
        label_cpf: "CPF / Tax ID",
        label_cnpj: "CNPJ / Business ID",
        label_phone: "Phone Number",
        label_address: "Address",
        label_area: "Area of Expertise (no accents, lowercase only)",
        placeholder_area: "e.g. software development",
        area_rule_warn: "Accents and uppercase letters are automatically removed.",
        label_modality: "Service Modality",
        chat_conversations: "Conversations",
        chat_select: "Select a contact",
        chat_placeholder: "Type your message...",
        no_profiles: "No profiles found matching these filters.",
        login_required_chat: "Please log in or create an account to send messages.",
        hello_user: "Hello,"
    },
    es: {
        loading_site: "Cargando plataforma...",
        loading_profiles: "Cargando perfiles...",
        loading_messages: "Cargando mensajes...",
        btn_login: "Iniciar Sesión",
        btn_register: "Crear Cuenta",
        btn_messages: "Mensajes",
        btn_logout: "Salir",
        btn_filter: "Filtrar",
        btn_send: "Enviar",
        btn_submit_register: "Finalizar Registro",
        btn_contact: "Enviar Mensaje",
        hero_title: "Encuentra Profesionales, Empresas y Proveedores de Servicios",
        hero_subtitle: "Conéctate directamente e intercambia mensajes en tiempo real con especialistas.",
        search_placeholder: "Buscar por nombre, área o dirección...",
        filter_all_types: "Todos los perfiles",
        filter_all_modalities: "Todas las modalidades",
        role_cliente: "Cliente",
        role_profissional: "Profesional",
        role_empresa: "Empresa",
        role_prestador: "Proveedor de Servicios",
        mod_remoto: "Remoto",
        mod_presencial: "Presencial",
        mod_hibrido: "Híbrido",
        label_account_type: "Tipo de Cuenta",
        label_fullname: "Nombre Completo",
        label_company_name: "Nombre de la Empresa",
        label_email: "Correo Electrónico",
        label_password: "Contraseña",
        label_age: "Edad",
        label_cpf: "CPF / Documento",
        label_cnpj: "CNPJ / Registro Fiscal",
        label_phone: "Número de Teléfono",
        label_address: "Dirección",
        label_area: "Área de Actuación (sin acentos ni mayúsculas)",
        placeholder_area: "ej: desarrollo de software",
        area_rule_warn: "Los acentos y las mayúsculas se eliminan automáticamente.",
        label_modality: "Modalidad de Servicio",
        chat_conversations: "Conversaciones",
        chat_select: "Selecciona un contacto",
        chat_placeholder: "Escribe tu mensaje...",
        no_profiles: "No se encontraron perfiles.",
        login_required_chat: "Inicia sesión o crea una cuenta para enviar mensajes.",
        hello_user: "Hola,"
    }
};

function detectBrowserLanguage() {
    const rawLang = (navigator.language || navigator.userLanguage || 'pt').toLowerCase();
    const shortLang = rawLang.split('-')[0];
    return TRANSLATIONS[shortLang] ? shortLang : 'en';
}

const CURRENT_LANG = detectBrowserLanguage();

function t(key) {
    return (TRANSLATIONS[CURRENT_LANG] && TRANSLATIONS[CURRENT_LANG][key]) || TRANSLATIONS['pt'][key] || key;
}

function applyTranslations() {
    document.documentElement.lang = CURRENT_LANG;
    const langLabel = document.getElementById('detected-lang-label');
    if (langLabel) langLabel.textContent = CURRENT_LANG.toUpperCase();

    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        el.textContent = t(key);
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        el.placeholder = t(key);
    });
}
