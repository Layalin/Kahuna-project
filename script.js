document.addEventListener('DOMContentLoaded', () => {
    // --- Global State ---
    let authToken = null;
    let currentUser = null;

    // --- Page Sections ---
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');
    const adminDashboard = document.getElementById('admin-dashboard');
    const clientDashboard = document.getElementById('client-dashboard');

    // --- Links to toggle forms ---
    const showRegisterLink = document.getElementById('show-register-form-link');
    const showLoginLink = document.getElementById('show-login-form-link');

    // --- Login Form ---
    const loginForm = document.getElementById('login-form');
    
    // --- Register Form ---
    const createAccountForm = document.getElementById('create-account-form');
    
    // --- Generic API Caller ---
    async function apiCall(endpoint, method = 'GET', body = null) {
        const options = { method, headers: { 'Content-Type': 'application/json' } };
        if (authToken) {
            options.headers['Authorization'] = `Bearer ${authToken}`;
        }
        if (body) {
            options.body = JSON.stringify(body);
        }
        const response = await fetch(`http://localhost/kahuna-api/${endpoint}`, options);
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'API request failed');
        return data;
    }

    // --- UI Toggling ---
    showRegisterLink.addEventListener('click', (e) => { e.preventDefault(); loginSection.classList.add('hidden'); registerSection.classList.remove('hidden'); });
    showLoginLink.addEventListener('click', (e) => { e.preventDefault(); registerSection.classList.add('hidden'); loginSection.classList.remove('hidden'); });

    // --- Login/Logout ---
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const loginMessage = document.getElementById('login-message');
        loginMessage.className = 'message-area';
        loginMessage.textContent = '';
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        try {
            const data = await apiCall('login.php', 'POST', { username, password });
            authToken = data.token;
            currentUser = data.user;
            loginSection.classList.add('hidden');

            if (currentUser.role === 'admin') {
                document.getElementById('admin-username').textContent = currentUser.username;
                adminDashboard.classList.remove('hidden');
                setupAdminDashboardListeners();
                adminFetchProducts();
            } else {
                document.getElementById('client-username').textContent = currentUser.username;
                clientDashboard.classList.remove('hidden');
                setupClientDashboardListeners();
                await clientPopulateProductDropdown();
                await clientFetchMyProducts();
            }
        } catch (error) {
            loginMessage.textContent = `Error: ${error.message}`;
            loginMessage.classList.add('error');
        }
    });

    function logout() {
        authToken = null; currentUser = null;
        adminDashboard.classList.add('hidden');
        clientDashboard.classList.add('hidden');
        registerSection.classList.add('hidden');
        loginSection.classList.remove('hidden');
        loginForm.reset();
    }
    
    // --- Account Creation ---
    createAccountForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const registerMessage = document.getElementById('register-message');
        registerMessage.className = 'message-area';
        registerMessage.textContent = '';
        const username = document.getElementById('new-username').value;
        const password = document.getElementById('new-password').value;
        try {
            await apiCall('register-user.php', 'POST', { username, password, role: 'client' });
            registerMessage.textContent = 'Account created successfully! Please log in.';
            registerMessage.classList.add('success');
        } catch (error) {
            registerMessage.textContent = `Error: ${error.message}`;
            registerMessage.classList.add('error');
        }
    });

    // --- Setup Listeners for Dashboards (This is the key change) ---
    function setupAdminDashboardListeners() {
        document.getElementById('logout-link').addEventListener('click', logout);
        document.getElementById('fetch-products-btn').addEventListener('click', adminFetchProducts);
        document.getElementById('toggle-user-form-btn').addEventListener('click', () => {
            document.getElementById('create-user-form').classList.toggle('hidden');
        });
        document.getElementById('create-user-form').addEventListener('submit', adminCreateUser);
    }

    function setupClientDashboardListeners() {
        document.getElementById('logout-link-client').addEventListener('click', logout);
        document.getElementById('register-product-form').addEventListener('submit', clientRegisterProduct);
    }

    // --- Admin Functions ---
    async function adminFetchProducts() {
        const filter = encodeURIComponent(document.getElementById('filter-username').value);
        const adminTableBody = adminDashboard.querySelector('.products-table tbody');
        const adminMessage = document.getElementById('admin-message');
        adminTableBody.innerHTML = '';
        adminMessage.textContent = 'Fetching...';
        adminMessage.className = 'message-area';
        try {
            const products = await apiCall(`view-products.php?username=${filter}`);
            adminMessage.className = 'message-area';
            adminMessage.textContent = '';
            if (products.length === 0) {
                 adminMessage.textContent = 'No matching products found.';
                 adminMessage.classList.add('success');
            }
            products.forEach(p => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${p.product_name}</td><td>${p.serial_number}</td><td>${p.purchase_date}</td><td>${p.registered_by_user}</td>`;
                adminTableBody.appendChild(row);
            });
        } catch (error) {
            adminMessage.textContent = `Error: ${error.message}`;
            adminMessage.classList.add('error');
        }
    }
    
    async function adminCreateUser(e) {
        e.preventDefault();
        const username = document.getElementById('admin-create-username').value;
        const password = document.getElementById('admin-create-password').value;
        const role = document.getElementById('admin-create-role').value;
        const adminMessage = document.getElementById('admin-message');
        try {
            await apiCall('register-user.php', 'POST', { username, password, role });
            adminMessage.textContent = `User '${username}' created successfully!`;
            adminMessage.className = 'message-area success';
            e.target.reset();
            e.target.classList.add('hidden');
        } catch (error) {
            adminMessage.textContent = `Error: ${error.message}`;
            adminMessage.classList.add('error');
        }
    }

    // --- Client Functions ---
    async function clientPopulateProductDropdown() {
        const productSelect = document.getElementById('register-serial');
        const clientMessage = document.getElementById('client-message');
        try {
            const products = await apiCall('get-products.php');
            productSelect.innerHTML = '<option value="">--Please choose a product--</option>';
            products.forEach(p => {
                const option = document.createElement('option');
                option.value = p.serial_number;
                option.textContent = `${p.product_name} (${p.serial_number})`;
                productSelect.appendChild(option);
            });
        } catch (error) {
            clientMessage.textContent = `Error loading products: ${error.message}`;
            clientMessage.classList.add('error');
        }
    }

    async function clientFetchMyProducts() {
        const clientTableBody = clientDashboard.querySelector('.products-table tbody');
        const clientMessage = document.getElementById('client-message');
        clientTableBody.innerHTML = '';
        try {
            const products = await apiCall('view-products.php');
            if (products.length === 0) {
                clientTableBody.innerHTML = '<tr><td colspan="4">You have not registered any products yet.</td></tr>';
            } else {
                products.forEach(p => {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td>${p.product_name}</td><td>${p.serial_number}</td><td>${p.purchase_date}</td><td>${p.warranty_days_left}</td>`;
                    clientTableBody.appendChild(row);
                });
            }
        } catch (error) {
            clientMessage.textContent = `Error: ${error.message}`;
            clientMessage.classList.add('error');
        }
    }

    async function clientRegisterProduct(e) {
        e.preventDefault();
        const serial_number = document.getElementById('register-serial').value;
        const purchase_date = document.getElementById('register-purchase-date').value;
        const clientMessage = document.getElementById('client-message');
        if (!serial_number || !purchase_date) {
            clientMessage.textContent = 'Please select a product and a purchase date.';
            clientMessage.className = 'message-area error';
            return;
        }
        try {
            await apiCall('register-product.php', 'POST', { serial_number, purchase_date });
            clientMessage.textContent = 'Product registered successfully!';
            clientMessage.className = 'message-area success';
            e.target.reset();
            clientFetchMyProducts();
        } catch (error) {
            clientMessage.textContent = `Error: ${error.message}`;
            clientMessage.classList.add('error');
        }
    }
});