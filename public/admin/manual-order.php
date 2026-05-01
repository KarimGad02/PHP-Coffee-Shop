<?php
// Assuming header.php includes your navbar, Bootstrap CSS, and your style.css
include_once __DIR__ . '/../includes/header.php'; 
// Fetch rooms directly
require_once __DIR__ . '/../../app/config/Database.php';
require_once __DIR__ . '/../../app/models/Room.php';
require_once __DIR__ . '/../../app/models/User.php';

$db = (new Database())->connect();
$rooms = (new Room($db))->getAll();
$users = (new User($db))->getAll();

?>

<div class="container-fluid mt-4">
    <div class="row gap-4 px-3">
        
        <div class="col-md-4 glass-card d-flex flex-column" style="max-height: 85vh; overflow-y: auto;">
            <h4 class="section-title mb-4">My Cart</h4>
            
            <div id="cart-items" class="flex-grow-1 mb-3">
                <p class="small">Your cart is empty. Click a product to add it!</p>
            </div>

            <div class="mt-auto border-top pt-3" style="border-color: var(--glass-border) !important;">
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea id="order-notes" class="form-control" rows="2" placeholder="e.g., 1 Tea Extra Sugar"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Room</label>
                    <select id="order-room" class="form-select">
                        <option value="" disabled selected>Select a room...</option>
                        <?php foreach($rooms as $room): ?>
                            <option value="<?= htmlspecialchars($room['id']) ?>">
                                <?= htmlspecialchars($room['name']) ?> (Ext: <?= htmlspecialchars($room['extension']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">User</label>
                    <select id="order-user" class="form-select">
                        <option value="" disabled selected>Select a user...</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?= htmlspecialchars($user['id']) ?>">
                                <?= htmlspecialchars($user['name']) ?> 
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white">Total:</h5>
                    <h3 class="mb-0 metric-value">EGP <span id="cart-total">0</span></h3>
                </div>

                <button id="confirm-order-btn" class="btn btn-coffee-primary w-100 py-2 fs-5">Confirm Order</button>
                <div id="order-message" class="mt-2 text-center"></div>
            </div>
        </div>

        <div class="col-md-7 d-flex flex-column gap-4">
            
            <div class="glass-card d-flex justify-content-between align-items-center p-3">
                <div class="w-50">
                    <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                </div>
                <div class="text-end">
                    <span class="eyebrow">Latest Order</span>
                    <div id="latest-order-container" class="d-flex gap-2 justify-content-end mt-2">
                        </div>
                </div>
            </div>

            <div class="glass-card flex-grow-1">
                <div class="row g-4" id="product-grid">
                    <div class="text-center w-100 py-5">
                        <div class="spinner-border text-warning" role="status"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // State Management
    let products = [];
    let cart = {}; // Format: { product_id: { product_obj, quantity } }
    
    // DOM Elements
    const productGrid = document.getElementById('product-grid');
    const searchInput = document.getElementById('product-search');
    const cartContainer = document.getElementById('cart-items');
    const cartTotalEl = document.getElementById('cart-total');
    const confirmBtn = document.getElementById('confirm-order-btn');
    const orderNotes = document.getElementById('order-notes');
    const orderRoom = document.getElementById('order-room');
    const orderUser = document.getElementById('order-user');
    const orderMessage = document.getElementById('order-message');

    // Fetch available products on load
    fetch('/api/menu')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                products = data.data;
                renderProducts(products);
            }
        });

    // Fetch latest order (Optional UI bonus based on specs)
    if (CURRENT_USER_ID) {
        fetch(`/api/orders/latest?user_id=${CURRENT_USER_ID}`)
            .then(res => res.json())
            .then(data => {
                if(data.success && data.data.length > 0) {
                    const container = document.getElementById('latest-order-container');
                    data.data.forEach(item => {
                        container.innerHTML += `<img src="${item.image}" alt="${item.name}" title="${item.name}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border: 1px solid var(--accent-secondary);">`;
                    });
                }
            });
    }

    // Render Products to Grid
    function renderProducts(itemsToRender) {
        productGrid.innerHTML = '';
        itemsToRender.forEach(prod => {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';
            col.innerHTML = `
                <div class="card bg-transparent border-0 text-center metric-card" style="cursor:pointer;" onclick="addToCart(${prod.id})">
                    <div class="position-relative d-inline-block mx-auto mb-2">
                        <img src="${prod.image}" alt="${prod.name}" class="img-fluid rounded-circle" style="width: 80px; height: 80px; object-fit: cover; border: 2px solid var(--glass-border);">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark" style="font-size:0.75rem;">
                            ${prod.price} LE
                        </span>
                    </div>
                    <h6 class="text-white mb-0">${prod.name}</h6>
                </div>
            `;
            productGrid.appendChild(col);
        });
    }

    // Search Filter
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const filtered = products.filter(p => p.name.toLowerCase().includes(term));
        renderProducts(filtered);
    });

    // Cart Logic (Global so inline onclick works)
    window.addToCart = (productId) => {
        const product = products.find(p => p.id === productId);
        if(!product) return;

        if (cart[productId]) {
            cart[productId].quantity += 1;
        } else {
            cart[productId] = { ...product, quantity: 1 };
        }
        updateCartUI();
    };

    window.changeQuantity = (productId, delta) => {
        if (!cart[productId]) return;
        cart[productId].quantity += delta;
        if (cart[productId].quantity <= 0) {
            delete cart[productId];
        }
        updateCartUI();
    };

    window.removeFromCart = (productId) => {
        delete cart[productId];
        updateCartUI();
    };

    // Update Cart UI & Total
    function updateCartUI() {
        cartContainer.innerHTML = '';
        let total = 0;

        const cartKeys = Object.keys(cart);
        if (cartKeys.length === 0) {
            cartContainer.innerHTML = '<p class="text-muted small">Your cart is empty. Click a product to add it!</p>';
            cartTotalEl.innerText = '0';
            return;
        }

        cartKeys.forEach(key => {
            const item = cart[key];
            const itemTotal = item.price * item.quantity;
            total += itemTotal;

            cartContainer.innerHTML += `
                <div class="d-flex justify-content-between align-items-center mb-3 bg-dark bg-opacity-25 p-2 rounded">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white">${item.name}</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-warning" onclick="changeQuantity(${item.id}, -1)">-</button>
                            <button type="button" class="btn btn-outline-warning" disabled>${item.quantity}</button>
                            <button type="button" class="btn btn-outline-warning" onclick="changeQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-warning">EGP ${itemTotal}</span>
                        <button class="btn btn-sm btn-danger rounded-circle" onclick="removeFromCart(${item.id})">X</button>
                    </div>
                </div>
            `;
        });

        cartTotalEl.innerText = total;
    }

    // Submit Order
    confirmBtn.addEventListener('click', async () => {
        const roomId = orderRoom.value;
        if (!roomId) {
            orderMessage.innerHTML = '<span class="text-danger">Please select a room!</span>';
            return;
        }
        const userId = orderUser.value;
        if (!userId) {
            orderMessage.innerHTML = '<span class="text-danger">Please select a user!</span>';
            return;
        }

        const items = Object.values(cart).map(item => ({
            product_id: item.id,
            quantity: item.quantity
        }));

        if (items.length === 0) {
            orderMessage.innerHTML = '<span class="text-danger">Cart is empty!</span>';
            return;
        }

        // Disable button during request
        confirmBtn.disabled = true;
        confirmBtn.innerText = 'Processing...';

        try {
            const payload = {
                user_id: userId, 
                room_id: roomId,
                notes: orderNotes.value,
                items: items,
                
            };

            const response = await fetch('/api/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                cart = {};
                updateCartUI();
                orderNotes.value = '';
                orderRoom.value = '';
                orderUser.value = '';
                orderMessage.innerHTML = '<span class="text-success">Order confirmed!</span>';
                
                // Refresh latest orders icon
                setTimeout(() => location.reload(), 1500); 
            } else {
                orderMessage.innerHTML = `<span class="text-danger">${result.message}</span>`;
            }
        } catch (error) {
            orderMessage.innerHTML = '<span class="text-danger">Network error occurred.</span>';
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerText = 'Confirm Order';
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>