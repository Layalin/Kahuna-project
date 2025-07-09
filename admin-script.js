document.addEventListener('DOMContentLoaded', () => {
    const fetchBtn = document.getElementById('fetch-products-btn');
    const tableBody = document.querySelector('#products-table tbody');
    const messageDiv = document.getElementById('admin-message');

    let authToken = null;

    async function loginAsAdmin() {
        messageDiv.textContent = 'Logging in as admin...';
        messageDiv.className = '';
        try {
            const response = await fetch('http://localhost/kahuna-api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: 'superadmin', password: 'adminpassword' })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Login failed');
            
            authToken = data.token;
            messageDiv.textContent = 'Logged in successfully. Ready to fetch products.';
            messageDiv.className = 'success';
        } catch (error) {
            messageDiv.textContent = `Login Error: ${error.message}`;
            messageDiv.className = 'error';
        }
    }

    async function fetchProducts() {
        if (!authToken) {
            messageDiv.textContent = 'Error: Not logged in. Please refresh the page.';
            messageDiv.className = 'error';
            return;
        }
        tableBody.innerHTML = '';
        messageDiv.textContent = 'Fetching...';
        try {
            const response = await fetch('http://localhost/kahuna-api/view-products.php', {
                method: 'GET',
                headers: { 'Authorization': `Bearer ${authToken}` }
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Fetch failed');
            
            if (data.length === 0) {
                messageDiv.textContent = 'No products have been registered yet.';
                messageDiv.className = 'success';
                return;
            }
            messageDiv.style.display = 'none';
            
            const tableHead = document.querySelector('#products-table thead tr');
            if (tableHead.children.length < 4) {
                 const th = document.createElement('th');
                 th.textContent = 'Registered By';
                 tableHead.appendChild(th);
            }

            data.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `<td>${product.product_name}</td><td>${product.serial_number}</td><td>${product.purchase_date}</td><td>${product.registered_by_user}</td>`;
                tableBody.appendChild(row);
            });
        } catch (error) {
            messageDiv.textContent = `Fetch Error: ${error.message}`;
            messageDiv.className = 'error';
        }
    }

    fetchBtn.addEventListener('click', fetchProducts);
    
    // Automatically log in as the admin when the page loads.
    loginAsAdmin();
});