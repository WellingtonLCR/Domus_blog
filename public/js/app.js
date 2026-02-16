// Configuração da API (base para todos os endpoints)
const API_BASE = new URL('index.php/api', window.location.href).pathname.replace(/\/$/, '');

// Estado global da aplicação (usuário logado, token e contexto de edição)
let currentUser = null;
let authToken = null;
let editingPostId = null;

// Inicializa a aplicação ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
    setupEventListeners();
});

// Registra todos os listeners de eventos da interface
function setupEventListeners() {
    // Formulário de login de Admin
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        adminLogin();
    });
    
    // Formulário de login de Usuário
    document.getElementById('usuarioLoginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        usuarioLogin();
    });
    
    // Formulário de criação de Post
    document.getElementById('createPostForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createPost();
    });
    
    // Formulário de criação de Usuário
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createUser();
    });
}

// Funções de autenticação (login/logout e controle de sessão)
async function adminLogin() {
    const usuario = document.getElementById('adminUsuario').value;
    const senha = document.getElementById('adminSenha').value;
    
    try {
        const response = await fetch(`${API_BASE}/auth/admin/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ usuario, senha })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            authToken = data.data.token;
            currentUser = data.data.admin;
            currentUser.type = 'admin';
            
            localStorage.setItem('authToken', authToken);
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            showToast('Login realizado com sucesso!', 'success');
            showDashboard();
        } else {
            showToast(data.message || 'Erro ao fazer login', 'error');
        }
    } catch (error) {
        showToast('Erro de conexão com o servidor', 'error');
        console.error('Login error:', error);
    }
}

async function deleteComment(id, postId) {
    if (!confirm('Tem certeza que deseja excluir este comentário?')) {
        return;
    }

    try {
        await apiRequest(`/comentarios/${id}`, {
            method: 'DELETE'
        });

        showToast('Comentário excluído com sucesso!', 'success');
        await loadPostComments(postId);
    } catch (error) {
        console.error('Erro ao excluir comentário:', error);
        showToast('Erro ao excluir comentário', 'error');
    }
}

async function usuarioLogin() {
    const usuario = document.getElementById('usuarioUsuario').value;
    const senha = document.getElementById('usuarioSenha').value;
    
    try {
        const response = await fetch(`${API_BASE}/auth/usuario/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ usuario, senha })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            authToken = data.data.token;
            currentUser = data.data.usuario;
            currentUser.type = 'usuario';
            
            localStorage.setItem('authToken', authToken);
            localStorage.setItem('currentUser', JSON.stringify(currentUser));
            
            showToast('Login realizado com sucesso!', 'success');
            showDashboard();
        } else {
            showToast(data.message || 'Erro ao fazer login', 'error');
        }
    } catch (error) {
        showToast('Erro de conexão com o servidor', 'error');
        console.error('Login error:', error);
    }
}

function checkAuth() {
    const token = localStorage.getItem('authToken');
    const user = localStorage.getItem('currentUser');
    
    if (token && user) {
        authToken = token;
        currentUser = JSON.parse(user);
        showDashboard();
    } else {
        showLogin();
    }
}

function logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('currentUser');
    authToken = null;
    currentUser = null;
    
    showToast('Logout realizado com sucesso!', 'success');
    showLogin();
}

// Navegação entre "páginas" (seções) do SPA
function showLogin() {
    hideAllPages();
    document.getElementById('loginPage').classList.remove('d-none');
    document.getElementById('navbar').classList.add('d-none');
}

function showDashboard() {
    hideAllPages();
    document.getElementById('homePage').classList.remove('d-none');
    document.getElementById('navbar').classList.remove('d-none');
    
    updateNavbar();
    loadDashboardData();
}

function showPage(pageName) {
    hideAllPages();
    
    switch(pageName) {
        case 'home':
            document.getElementById('homePage').classList.remove('d-none');
            loadDashboardData();
            break;
        case 'posts':
            document.getElementById('postsPage').classList.remove('d-none');
            loadPosts();
            break;
        case 'users':
            document.getElementById('usersPage').classList.remove('d-none');
            loadUsers();
            break;
        case 'comments':
            document.getElementById('commentsPage').classList.remove('d-none');
            loadComments();
            break;
    }
}

function hideAllPages() {
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.add('d-none'));
}

function updateNavbar() {
    // Mostra/oculta itens de navegação conforme o tipo de usuário
    const isAdmin = currentUser.type === 'admin';
    
    document.getElementById('navHome').classList.remove('d-none');
    document.getElementById('navPosts').classList.remove('d-none');
    document.getElementById('navUsers').classList.toggle('d-none', !isAdmin);
    document.getElementById('navComments').classList.remove('d-none');
    document.getElementById('userDropdown').classList.remove('d-none');
    
    document.getElementById('userNameDisplay').textContent = currentUser.nome;
}

// Funções auxiliares para chamadas à API
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
    };
    
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }
    
    console.log('Making request to:', url);
    console.log('Headers:', headers);
    
    try {
        const response = await fetch(url, {
            ...options,
            headers
        });
        
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);
        
        if (!response.ok) {
            const contentType = response.headers.get('content-type') || '';
            let errorPayload;

            if (contentType.includes('application/json')) {
                try {
                    errorPayload = await response.json();
                } catch {
                    errorPayload = null;
                }
            } else {
                try {
                    errorPayload = await response.text();
                } catch {
                    errorPayload = null;
                }
            }

            console.error('Response error:', errorPayload);

            let message = `HTTP ${response.status}`;
            if (errorPayload && typeof errorPayload === 'object') {
                if (errorPayload.message) message = errorPayload.message;
                const firstFieldErrors = errorPayload.errors ? Object.values(errorPayload.errors)[0] : null;
                if (Array.isArray(firstFieldErrors) && firstFieldErrors[0]) message = firstFieldErrors[0];
            } else if (typeof errorPayload === 'string' && errorPayload.trim()) {
                message = errorPayload;
            }

            const err = new Error(message);
            err.status = response.status;
            err.payload = errorPayload;
            throw err;
        }

        const data = await response.json();
        console.log('Response data:', data);
        
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        throw error;
    }
}

// Funções relacionadas ao Dashboard (cards e lista de posts recentes)
async function loadDashboardData() {
    try {
        // Carrega posts publicados (não requer autenticação)
        const postsResponse = await apiRequest('/posts/published');
        const posts = postsResponse.data || [];
        
        // Carrega usuários (somente quando houver token válido)
        let users = [];
        if (authToken) {
            try {
                const usersResponse = await apiRequest('/usuarios');
                users = usersResponse.data || [];
            } catch (userError) {
                console.warn('Could not load users:', userError);
            }
        }
        
        // Atualiza os indicadores do painel
        const bannedCount = users.filter(u => u.banido_em).length;
        const activeCount = users.length - bannedCount;

        document.getElementById('totalPosts').textContent = posts.length;
        document.getElementById('totalUsers').textContent = activeCount;
        document.getElementById('bannedUsers').textContent = bannedCount;
        document.getElementById('publishedPosts').textContent = posts.filter(p => p.status === 'publicado').length;

        // Total de comentários (disponível para admin autenticado)
        if (authToken && currentUser && currentUser.type === 'admin') {
            try {
                const commentsResponse = await apiRequest('/comentarios');
                const comments = commentsResponse.data || [];
                document.getElementById('totalComments').textContent = comments.length;
            } catch (e) {
                document.getElementById('totalComments').textContent = '—';
            }
        } else {
            document.getElementById('totalComments').textContent = '—';
        }
        
        // Renderiza os posts recentes
        loadRecentPosts(posts);
    } catch (error) {
        console.error('Dashboard error:', error);
        showToast('Erro ao carregar dados do dashboard', 'error');
    }
}

function loadRecentPosts(posts) {
    const container = document.getElementById('recentPosts');
    
    if (posts.length === 0) {
        container.innerHTML = '<div class="col-12"><p class="text-muted">Nenhum post encontrado.</p></div>';
        return;
    }
    
    container.innerHTML = posts.slice(0, 6).map(post => `
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">${post.titulo}</h5>
                    <p class="card-text text-muted small">
                        Por ${post.usuario?.nome || 'Desconhecido'} • ${formatDate(post.data_criacao)}
                    </p>
                    <p class="card-text">${truncateText(post.texto || post.conteudo, 100)}</p>
                    <span class="badge bg-${getStatusColor(post.status)}">${post.status}</span>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-outline-primary" onclick="viewPost(${post.id})">
                        <i class="fas fa-eye me-1"></i>Ver
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Funções de gestão de Posts (listagem, filtros, criação e ações)
async function loadPosts() {
    try {
        // Busca todos os posts da API
        const response = await apiRequest('/posts');
        let posts = response.data || [];

        // Aplica filtros da interface (status e busca por texto)
        const statusSelect = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchPosts');

        const statusFilter = statusSelect ? statusSelect.value : '';
        const searchTerm = searchInput ? searchInput.value.trim().toLowerCase() : '';

        if (statusFilter) {
            posts = posts.filter(post => post.status === statusFilter);
        }

        if (searchTerm) {
            posts = posts.filter(post => {
                const titulo = (post.titulo || '').toLowerCase();
                const texto = (post.texto || post.conteudo || '').toLowerCase();
                return titulo.includes(searchTerm) || texto.includes(searchTerm);
            });
        }

        const container = document.getElementById('postsList');

        if (posts.length === 0) {
            container.innerHTML = '<p class="text-muted">Nenhum post encontrado.</p>';
            return;
        }

        container.innerHTML = posts.map(post => `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title">${post.titulo}</h5>
                            <p class="text-muted small">
                                Por ${post.usuario?.nome || 'Desconhecido'} • ${formatDate(post.data_criacao)}
                            </p>
                            <p class="card-text">${truncateText(post.texto || post.conteudo, 200)}</p>
                            <span class="badge bg-${getStatusColor(post.status)} me-2">${post.status}</span>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-sm btn-outline-primary me-2" onclick="viewPost(${post.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${canEditPost(post) ? `
                                <button class="btn btn-sm btn-outline-warning me-2" onclick="editPost(${post.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deletePost(${post.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        showToast('Erro ao carregar posts', 'error');
    }
}

async function savePost() {
    const title = document.getElementById('postTitle').value;
    const status = document.getElementById('postStatus').value;
    const content = document.getElementById('postContent').value;
    
    try {
        if (editingPostId) {
            await apiRequest(`/posts/${editingPostId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    titulo: title,
                    status: status,
                    texto: content
                })
            });
            showToast('Post atualizado com sucesso!', 'success');
        } else {
            await apiRequest('/posts', {
                method: 'POST',
                body: JSON.stringify({
                    titulo: title,
                    status: status,
                    texto: content
                })
            });
            showToast('Post criado com sucesso!', 'success');
        }
        
        // Fecha o modal e limpa o formulário de criação de post
        const modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
        modal.hide();
        document.getElementById('createPostForm').reset();
        editingPostId = null;
        const saveBtn = document.getElementById('savePostButton');
        if (saveBtn) saveBtn.textContent = 'Criar Post';
        
        // Recarrega a listagem de posts se a página de posts estiver visível
        if (!document.getElementById('postsPage').classList.contains('d-none')) {
            loadPosts();
        } else {
            loadDashboardData();
        }
    } catch (error) {
        showToast(error?.message || 'Erro ao criar post', 'error');
    }
}

// Funções de gestão de Usuários (listagem e criação)
async function loadUsers() {
    try {
        const response = await apiRequest('/usuarios');
        const users = response.data || [];
        
        const container = document.getElementById('usersList');
        
        if (users.length === 0) {
            container.innerHTML = '<p class="text-muted">Nenhum usuário encontrado.</p>';
            return;
        }
        
        container.innerHTML = users.map(user => `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title">${user.nome}</h5>
                            <p class="text-muted">@${user.usuario}</p>
                            <p class="card-text">${user.biografia || 'Sem biografia'}</p>
                            ${user.banido_em ? '<span class="badge bg-danger">Banido</span>' : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            ${currentUser.type === 'admin' ? `
                                ${!user.banido_em ? `
                                    <button class="btn btn-sm btn-outline-warning me-2" onclick="banUser(${user.id})">
                                        <i class="fas fa-ban"></i> Banir
                                    </button>
                                ` : `
                                    <button class="btn btn-sm btn-outline-success me-2" onclick="unbanUser(${user.id})">
                                        <i class="fas fa-check"></i> Desbanir
                                    </button>
                                `}
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        showToast('Erro ao carregar usuários', 'error');
    }
}

async function createUser() {
    const name = document.getElementById('newUserName').value;
    const usuario = document.getElementById('newUserUsuario').value;
    const senha = document.getElementById('newUserSenha').value;
    const biografia = document.getElementById('newUserBiografia').value;
    
    try {
        await apiRequest('/usuarios', {
            method: 'POST',
            body: JSON.stringify({
                nome: name,
                usuario: usuario,
                senha: senha,
                biografia: biografia
            })
        });
        
        showToast('Usuário criado com sucesso!', 'success');
        
        // Close modal and reset form
        const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
        modal.hide();
        document.getElementById('createUserForm').reset();
        
        // Recarrega a listagem de usuários
        loadUsers();
    } catch (error) {
        showToast('Erro ao criar usuário', 'error');
    }
}

// Funções de comentários (listagem na aba Comentários)
async function loadComments() {
    try {
        const container = document.getElementById('commentsList');

        if (!authToken || !currentUser) {
            container.innerHTML = '<p class="text-muted">Faça login para visualizar comentários.</p>';
            return;
        }

        let comentarios = [];

        if (currentUser.type === 'admin') {
            // Admin visualiza o painel completo de comentários
            const response = await apiRequest('/comentarios');
            comentarios = response.data || [];
        } else {
            // Usuário comum visualiza comentários feitos nos seus próprios posts
            const response = await apiRequest('/comentarios/my-posts');
            comentarios = response.data || [];
        }

        if (!comentarios.length) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h5>Nenhum comentário registrado.</h5>
                    <p>Os comentários dos posts aparecerão aqui conforme forem sendo criados.</p>
                </div>`;
            return;
        }

        container.innerHTML = comentarios.map(c => `
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="comment-author">${c.usuario?.nome || c.usuario?.usuario || 'Usuário'}</div>
                            <div class="comment-date">${c.data_criacao ? new Date(c.data_criacao).toLocaleString('pt-BR') : ''}</div>
                            <div class="mt-2">${c.texto}</div>
                            ${c.post ? `<small class="text-muted">No post: "${c.post.titulo}"</small>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Erro ao carregar comentários:', error);
        showToast('Erro ao carregar comentários', 'error');
    }
}

// Funções auxiliares para abrir modais
function showCreatePostModal() {
    editingPostId = null;
    const form = document.getElementById('createPostForm');
    form.reset();
    const saveBtn = document.getElementById('savePostButton');
    if (saveBtn) saveBtn.textContent = 'Criar Post';
    const modal = new bootstrap.Modal(document.getElementById('createPostModal'));
    modal.show();
}

function showCreateUserModal() {
    const modal = new bootstrap.Modal(document.getElementById('createUserModal'));
    modal.show();
}

// Funções utilitárias (toasts, tratamento de erros, etc.)
function showToast(message, type = 'info') {
    const toastElement = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    toastMessage.textContent = message;
    
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

function getStatusColor(status) {
    switch(status) {
        case 'rascunho': return 'secondary';
        case 'publicado': return 'success';
        case 'arquivado': return 'warning';
        default: return 'primary';
    }
}

function canEditPost(post) {
    if (currentUser.type === 'admin') return true;
    if (currentUser.type === 'usuario' && post.usuario_id === currentUser.id) return true;
    return false;
}

// Funções relacionadas à visualização de um post em modal
async function viewPost(id) {
    try {
        const response = await apiRequest(`/posts/${id}`);
        const post = response.data;

        const titleEl = document.getElementById('viewPostTitle');
        const metaEl = document.getElementById('viewPostMeta');
        const contentEl = document.getElementById('viewPostContent');

        if (!post) {
            showToast('Post não encontrado', 'error');
            return;
        }

        titleEl.textContent = post.titulo || 'Post';

        const autorNome = post.usuario?.nome || post.usuario?.usuario || '—';
        const dataCriacao = post.data_criacao || post.created_at || null;
        const dataFormatada = dataCriacao ? new Date(dataCriacao).toLocaleString('pt-BR') : '—';
        const status = post.status || '—';

        metaEl.textContent = `Por ${autorNome} • ${dataFormatada} • ${status}`;
        contentEl.textContent = post.texto || '';

        // Carrega comentários do post
        await loadPostComments(id);

        const modal = new bootstrap.Modal(document.getElementById('viewPostModal'));
        modal.show();
    } catch (error) {
        console.error('Erro ao carregar post:', error);
        showToast('Erro ao carregar post', 'error');
    }
}

async function editPost(id) {
    try {
        // Usa a rota autenticada para garantir que o backend
        // reconheça o usuário logado ao aplicar as Gates
        const response = await apiRequest(`/posts/auth/${id}`);
        const post = response.data;

        if (!post) {
            showToast('Post não encontrado', 'error');
            return;
        }

        editingPostId = id;
        document.getElementById('postTitle').value = post.titulo || '';
        document.getElementById('postStatus').value = post.status || 'rascunho';
        document.getElementById('postContent').value = post.texto || '';

        const saveBtn = document.getElementById('savePostButton');
        if (saveBtn) saveBtn.textContent = 'Salvar Alterações';

        const modal = new bootstrap.Modal(document.getElementById('createPostModal'));
        modal.show();
    } catch (error) {
        console.error('Erro ao carregar post para edição:', error);
        showToast('Erro ao carregar post para edição', 'error');
    }
}

async function loadPostComments(postId) {
    const listEl = document.getElementById('postCommentsList');
    const formWrapper = document.getElementById('postCommentFormWrapper');
    const hintEl = document.getElementById('commentsHint');

    if (!listEl || !hintEl) return;

    listEl.innerHTML = '<p class="text-muted">Carregando comentários...</p>';

    try {
        const response = await apiRequest(`/comentarios/post/${postId}`);
        const comentarios = response.data?.data || response.data || [];

        if (!comentarios.length) {
            listEl.innerHTML = '<p class="text-muted mb-0">Nenhum comentário ainda. Seja o primeiro a comentar!</p>';
        } else {
            listEl.innerHTML = comentarios.map(c => {
                const isAdmin = currentUser && currentUser.type === 'admin';
                const isOwner = currentUser && currentUser.type === 'usuario' && Number(c.usuario_id) === Number(currentUser.id);

                const canDelete = isAdmin || isOwner;

                return `
                <div class="comment-card mb-2 d-flex justify-content-between align-items-start">
                    <div>
                        <div class="comment-author">${c.usuario?.nome || c.usuario?.usuario || 'Usuário'}</div>
                        <div class="comment-date">${c.data_criacao ? new Date(c.data_criacao).toLocaleString('pt-BR') : ''}</div>
                        <div class="mt-1">${c.texto}</div>
                    </div>
                    ${canDelete ? `
                        <button class="btn btn-sm btn-outline-danger ms-2" title="Excluir comentário" onclick="deleteComment(${c.id}, ${postId})">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>`;
            }).join('');
        }

        // Controle de permissão para comentar
        if (currentUser && currentUser.type === 'usuario') {
            formWrapper?.classList.remove('d-none');
            formWrapper.dataset.postId = postId;
            hintEl.textContent = '';
        } else {
            formWrapper?.classList.add('d-none');
            hintEl.textContent = 'Apenas usuários autenticados podem comentar em posts.';
        }
    } catch (error) {
        console.error('Erro ao carregar comentários:', error);
        listEl.innerHTML = '<p class="text-muted text-danger">Erro ao carregar comentários.</p>';
    }
}

async function submitComment() {
    const formWrapper = document.getElementById('postCommentFormWrapper');
    const textarea = document.getElementById('newCommentText');
    if (!formWrapper || !textarea) return;

    const postId = formWrapper.dataset.postId;
    const texto = textarea.value.trim();

    if (!texto) {
        showToast('Digite um comentário antes de enviar.', 'warning');
        return;
    }

    try {
        await apiRequest('/comentarios', {
            method: 'POST',
            body: JSON.stringify({
                post_id: postId,
                texto: texto
            })
        });

        textarea.value = '';
        showToast('Comentário criado com sucesso!', 'success');
        await loadPostComments(postId);
    } catch (error) {
        console.error('Erro ao criar comentário:', error);
        showToast('Erro ao criar comentário', 'error');
    }
}

async function deletePost(id) {
    if (!confirm('Tem certeza que deseja excluir este post? Esta ação não pode ser desfeita.')) {
        return;
    }

    try {
        await apiRequest(`/posts/${id}`, {
            method: 'DELETE'
        });

        showToast('Post excluído com sucesso!', 'success');

        // Atualiza listagens após exclusão
        if (!document.getElementById('postsPage').classList.contains('d-none')) {
            await loadPosts();
        } else {
            await loadDashboardData();
        }
    } catch (error) {
        console.error('Erro ao excluir post:', error);
        showToast('Erro ao excluir post', 'error');
    }
}

async function banUser(id) {
    if (!confirm('Tem certeza que deseja banir este usuário?')) {
        return;
    }

    try {
        await apiRequest(`/usuarios/${id}/ban`, {
            method: 'POST'
        });

        showToast('Usuário banido com sucesso!', 'success');
        await loadUsers();
    } catch (error) {
        console.error('Erro ao banir usuário:', error);
        showToast('Erro ao banir usuário', 'error');
    }
}

async function unbanUser(id) {
    if (!confirm('Tem certeza que deseja desbanir este usuário?')) {
        return;
    }

    try {
        await apiRequest(`/usuarios/${id}/unban`, {
            method: 'POST'
        });

        showToast('Usuário desbanido com sucesso!', 'success');
        await loadUsers();
    } catch (error) {
        console.error('Erro ao desbanir usuário:', error);
        showToast('Erro ao desbanir usuário', 'error');
    }
}

async function showProfile() {
    if (!currentUser || !authToken) {
        showToast('É necessário estar autenticado para editar o perfil.', 'warning');
        return;
    }

    // Preenche o modal com os dados atuais do usuário em memória
    const nameInput = document.getElementById('profileName');
    const usuarioInput = document.getElementById('profileUsuario');
    const bioInput = document.getElementById('profileBiografia');
    const senhaInput = document.getElementById('profileSenha');

    if (nameInput) nameInput.value = currentUser.nome || '';
    if (usuarioInput) usuarioInput.value = currentUser.usuario || '';
    if (bioInput) bioInput.value = currentUser.biografia || '';
    if (senhaInput) senhaInput.value = '';

    const modal = new bootstrap.Modal(document.getElementById('editProfileModal'));
    modal.show();
}

async function saveProfile() {
    if (!currentUser || !authToken) {
        showToast('É necessário estar autenticado para editar o perfil.', 'warning');
        return;
    }

    const nameInput = document.getElementById('profileName');
    const bioInput = document.getElementById('profileBiografia');
    const senhaInput = document.getElementById('profileSenha');

    const payload = {
        nome: nameInput ? nameInput.value : currentUser.nome,
        biografia: bioInput ? bioInput.value : currentUser.biografia
    };

    // Só envia senha se o usuário informou uma nova
    if (senhaInput && senhaInput.value.trim()) {
        payload.senha = senhaInput.value.trim();
    }

    try {
        // Define endpoint conforme o tipo de usuário logado
        let endpoint = '';
        if (currentUser.type === 'admin') {
            endpoint = `/admins/${currentUser.id}`;
        } else {
            endpoint = `/usuarios/${currentUser.id}`;
        }

        const response = await apiRequest(endpoint, {
            method: 'PUT',
            body: JSON.stringify(payload)
        });

        // Atualiza o currentUser no frontend com os dados retornados, se houver
        const updated = response.data || {};
        currentUser.nome = updated.nome || payload.nome;
        currentUser.biografia = updated.biografia ?? payload.biografia;

        localStorage.setItem('currentUser', JSON.stringify(currentUser));
        updateNavbar();

        const modal = bootstrap.Modal.getInstance(document.getElementById('editProfileModal'));
        if (modal) modal.hide();

        showToast('Perfil atualizado com sucesso!', 'success');
    } catch (error) {
        console.error('Erro ao atualizar perfil:', error);
        showToast(error?.message || 'Erro ao atualizar perfil', 'error');
    }
}
