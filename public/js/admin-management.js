async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options
    });

    let data = {};
    try {
        data = await response.json();
    } catch (error) {
        data = { success: false, message: 'Invalid server response' };
    }

    if (!response.ok && data.success !== false) {
        data.success = false;
        data.message = data.message || 'Request failed';
    }

    return data;
}

function showMessage(elementId, text, isSuccess) {
    const el = document.getElementById(elementId);
    if (!el) return;

    el.textContent = text;
    el.className = isSuccess ? 'alert alert-success mt-3' : 'alert alert-danger mt-3';
    el.style.display = 'block';
}

async function ensureAdmin() {
    const me = await apiRequest('/auth/me');
    if (!me.success || !me.data || me.data.role !== 'admin') {
        window.location.href = '/login.php';
        return false;
    }
    return true;
}

function renderUsersTable(users) {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    if (!Array.isArray(users) || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-light">No users found.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map((user) => {
        const imageCell = user.profile_image
            ? `<img src="${user.profile_image}" alt="Profile" style="width:42px;height:42px;border-radius:8px;object-fit:cover;">`
            : '<span class="text-muted">-</span>';

        return `
            <tr>
                <td>${user.id}</td>
                <td>${escapeHtml(user.name || '')}</td>
                <td>${escapeHtml(user.email || '')}</td>
                <td>${escapeHtml(user.room_number || '')}</td>
                <td>${escapeHtml(user.extension || '')}</td>
                <td>${escapeHtml(user.role || '')}</td>
                <td>${imageCell}</td>
                <td>
                    <button class="btn btn-sm btn-coffee-secondary me-2" onclick="editUser(${user.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderProductsTable(products) {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;

    if (!Array.isArray(products) || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-light">No products found.</td></tr>';
        return;
    }

    tbody.innerHTML = products.map((product) => {
        const imageCell = product.image
            ? `<img src="${product.image}" alt="Product" style="width:42px;height:42px;border-radius:8px;object-fit:cover;">`
            : '<span class="text-muted">-</span>';

        const statusBadge = Number(product.is_available) === 1
            ? '<span class="badge bg-success">Available</span>'
            : '<span class="badge bg-secondary">Unavailable</span>';

        return `
            <tr>
                <td>${product.id}</td>
                <td>${escapeHtml(product.name || '')}</td>
                <td>${Number(product.price).toFixed(2)}</td>
                <td>${escapeHtml(product.category_name || '')}</td>
                <td>${statusBadge}</td>
                <td>${imageCell}</td>
                <td>
                    <button class="btn btn-sm btn-coffee-secondary me-2" onclick="editProduct(${product.id})">Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                </td>
            </tr>
        `;
    }).join('');
}

function fillCategorySelect(categories) {
    const select = document.getElementById('category_id');
    if (!select) return;

    select.innerHTML = '<option value="">Select category</option>';
    (categories || []).forEach((category) => {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        select.appendChild(option);
    });
}

async function loadUsers() {
    const response = await apiRequest('/admin/users');
    if (!response.success) {
        showMessage('usersMessage', response.message || 'Failed to load users', false);
        return;
    }
    renderUsersTable(response.data || []);
}

async function loadProducts() {
    const response = await apiRequest('/admin/products');
    if (!response.success) {
        showMessage('productsMessage', response.message || 'Failed to load products', false);
        return;
    }
    renderProductsTable(response.data || []);
}

async function loadCategories() {
    const response = await apiRequest('/admin/categories');
    if (!response.success) {
        showMessage('productFormMessage', response.message || 'Failed to load categories', false);
        return;
    }
    fillCategorySelect(response.data || []);
}

async function deleteUser(id) {
    if (!confirm('Delete this user?')) return;

    const response = await apiRequest(`/admin/users/${id}`, { method: 'DELETE' });
    if (!response.success) {
        showMessage('usersMessage', response.message || 'Failed to delete user', false);
        return;
    }

    showMessage('usersMessage', response.message || 'User deleted', true);
    loadUsers();
}

async function deleteProduct(id) {
    if (!confirm('Delete this product?')) return;

    const response = await apiRequest(`/admin/products/${id}`, { method: 'DELETE' });
    if (!response.success) {
        showMessage('productsMessage', response.message || 'Failed to delete product', false);
        return;
    }

    showMessage('productsMessage', response.message || 'Product deleted', true);
    loadProducts();
}

function editUser(id) {
    window.location.href = `/admin/edit-user.html?id=${id}`;
}

function editProduct(id) {
    window.location.href = `/admin/edit-product.html?id=${id}`;
}

function getQueryParam(name) {
    return new URLSearchParams(window.location.search).get(name);
}

async function loadUserEditForm() {
    const id = getQueryParam('id');
    if (!id) {
        showMessage('userEditMessage', 'Missing user id', false);
        return;
    }

    const response = await apiRequest(`/admin/users/${id}`);
    if (!response.success || !response.data) {
        showMessage('userEditMessage', response.message || 'Could not load user', false);
        return;
    }

    document.getElementById('userId').value = response.data.id;
    document.getElementById('name').value = response.data.name || '';
    document.getElementById('email').value = response.data.email || '';
    document.getElementById('room_number').value = response.data.room_number || '';
    document.getElementById('extension').value = response.data.extension || '';
    document.getElementById('role').value = response.data.role || 'customer';

    const preview = document.getElementById('profileImagePreview');
    if (preview && response.data.profile_image) {
        preview.src = response.data.profile_image;
        preview.style.display = 'block';
    }
}

async function loadProductEditForm() {
    const id = getQueryParam('id');
    if (!id) {
        showMessage('productEditMessage', 'Missing product id', false);
        return;
    }

    const [productResponse, categoriesResponse] = await Promise.all([
        apiRequest(`/admin/products/${id}`),
        apiRequest('/admin/categories')
    ]);

    if (!productResponse.success || !productResponse.data) {
        showMessage('productEditMessage', productResponse.message || 'Could not load product', false);
        return;
    }

    if (categoriesResponse.success) {
        fillCategorySelect(categoriesResponse.data || []);
    }

    document.getElementById('productId').value = productResponse.data.id;
    document.getElementById('name').value = productResponse.data.name || '';
    document.getElementById('price').value = productResponse.data.price || '';
    document.getElementById('category_id').value = productResponse.data.category_id || '';
    document.getElementById('is_available').value = String(productResponse.data.is_available ?? 1);

    const preview = document.getElementById('productImagePreview');
    if (preview && productResponse.data.image) {
        preview.src = productResponse.data.image;
        preview.style.display = 'block';
    }
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function logoutSystem() {
    fetch('/auth/logout', { method: 'POST', credentials: 'same-origin' })
        .then(() => {
            window.location.href = '/login.php';
        })
        .catch((error) => console.error('Error logging out:', error));
}

document.addEventListener('DOMContentLoaded', async () => {
    const allowed = await ensureAdmin();
    if (!allowed) return;

    document.querySelectorAll('.logout-btn').forEach((btn) => {
        btn.addEventListener('click', logoutSystem);
    });

    const page = document.body.getAttribute('data-page');

    if (page === 'all-users') {
        loadUsers();
    }

    if (page === 'add-user') {
        const form = document.getElementById('addUserForm');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(form);
            const response = await apiRequest('/admin/users', {
                method: 'POST',
                body: formData
            });

            if (!response.success) {
                showMessage('userFormMessage', response.message || 'Failed to create user', false);
                return;
            }

            form.reset();
            showMessage('userFormMessage', response.message || 'User created successfully', true);
        });
    }

    if (page === 'edit-user') {
        await loadUserEditForm();

        const form = document.getElementById('editUserForm');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const id = document.getElementById('userId').value;
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const response = await apiRequest(`/admin/users/${id}`, {
                method: 'POST',
                body: formData
            });

            if (!response.success) {
                showMessage('userEditMessage', response.message || 'Failed to update user', false);
                return;
            }

            showMessage('userEditMessage', response.message || 'User updated successfully', true);
        });
    }

    if (page === 'all-products') {
        loadProducts();
    }

    if (page === 'add-product') {
        loadCategories();

        const productForm = document.getElementById('addProductForm');
        const categoryForm = document.getElementById('quickCategoryForm');

        productForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(productForm);
            const response = await apiRequest('/admin/products', {
                method: 'POST',
                body: formData
            });

            if (!response.success) {
                showMessage('productFormMessage', response.message || 'Failed to create product', false);
                return;
            }

            productForm.reset();
            showMessage('productFormMessage', 'Product added successfully', true);
        });

        categoryForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const categoryInput = document.getElementById('newCategoryName');
            const response = await apiRequest('/admin/categories', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: categoryInput.value })
            });

            if (!response.success) {
                showMessage('categoryMessage', response.message || 'Failed to create category', false);
                return;
            }

            categoryInput.value = '';
            showMessage('categoryMessage', 'Category added', true);
            await loadCategories();
        });
    }

    if (page === 'edit-product') {
        await loadProductEditForm();

        const form = document.getElementById('editProductForm');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const id = document.getElementById('productId').value;
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const response = await apiRequest(`/admin/products/${id}`, {
                method: 'POST',
                body: formData
            });

            if (!response.success) {
                showMessage('productEditMessage', response.message || 'Failed to update product', false);
                return;
            }

            showMessage('productEditMessage', response.message || 'Product updated successfully', true);
        });
    }
});
