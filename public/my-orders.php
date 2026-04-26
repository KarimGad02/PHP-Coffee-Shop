<?php
// Include the header (which handles our session and authentication protection)
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="container-fluid mt-4 px-4">
    <h2 class="section-title mb-4">My Orders</h2>

    <div class="glass-card mb-4 p-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Date from</label>
                <input type="date" id="date-from" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date to</label>
                <input type="date" id="date-to" class="form-control">
            </div>
            <div class="col-md-4">
                <button id="filter-btn" class="btn btn-coffee-primary w-100">Apply Filter</button>
            </div>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-glass table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="padding-left: 20px;">Order Date</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th class="text-end" style="padding-right: 20px;">Action</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody">
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="spinner-border text-warning" role="status"></div>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="border-top" style="border-color: var(--glass-border) !important;">
                    <tr>
                        <td colspan="4" class="text-end py-3" style="padding-right: 20px;">
                            <h4 class="mb-0 text-white">Total <span class="metric-value ms-3">EGP <span id="grand-total">0</span></span></h4>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('orders-tbody');
    const grandTotalEl = document.getElementById('grand-total');
    const dateFrom = document.getElementById('date-from');
    const dateTo = document.getElementById('date-to');
    const filterBtn = document.getElementById('filter-btn');

    // Fetch Orders Function
    function fetchOrders() {
        let url = `/api/orders?user_id=${CURRENT_USER_ID}`;
        
        if (dateFrom.value) url += `&date_from=${dateFrom.value}`;
        if (dateTo.value) url += `&date_to=${dateTo.value}`;

        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4">Loading...</td></tr>';

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderOrders(data.data);
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${data.message || 'Failed to load orders'}</td></tr>`;
                }
            })
            .catch(err => {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Network Error</td></tr>';
            });
    }

    // Render Orders to Table
    function renderOrders(orders) {
        tbody.innerHTML = '';
        let grandTotal = 0;

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No orders found for this period.</td></tr>';
            grandTotalEl.innerText = '0';
            return;
        }

        orders.forEach(order => {
            grandTotal += parseFloat(order.total_amount);
            
            // Format date slightly better
            const orderDate = new Date(order.created_at).toLocaleString();
            
            // Determine Cancel Button state
            let actionHtml = '';
            if (order.status.toLowerCase() === 'processing') {
                actionHtml = `<button class="btn btn-sm btn-outline-danger px-3 rounded-pill" onclick="cancelOrder(${order.id})">CANCEL</button>`;
            }

            // Main Order Row
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="padding-left: 20px;">
                    <button class="btn btn-sm btn-coffee-secondary me-2 rounded-circle" style="width:30px; height:30px; padding:0;" onclick="toggleItems(${order.id})">
                        <span id="icon-${order.id}">+</span>
                    </button>
                    ${orderDate}
                </td>
                <td>
                    <span class="badge ${order.status === 'done' ? 'bg-success' : (order.status === 'processing' ? 'bg-warning text-dark' : 'bg-secondary')}">
                        ${order.status.toUpperCase()}
                    </span>
                </td>
                <td class="text-warning fw-bold">${order.total_amount} EGP</td>
                <td class="text-end" style="padding-right: 20px;">${actionHtml}</td>
            `;
            tbody.appendChild(row);

            // Sub-row for Order Items (Hidden by default)
            let itemsHtml = '<div class="d-flex flex-wrap justify-content-center gap-4 py-3 px-4">';
            order.items.forEach(item => {
                itemsHtml += `
                    <div class="text-center position-relative">
                        <img src="${item.image}" alt="${item.name}" class="rounded" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid var(--glass-border);">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark border border-warning text-warning" style="font-size:0.7rem;">
                            ${item.historical_price} LE
                        </span>
                        <div class="mt-1 small text-white">${item.name}</div>
                        <div class="text-muted small">Qty: ${item.quantity}</div>
                    </div>
                `;
            });
            itemsHtml += '</div>';

            const itemsRow = document.createElement('tr');
            itemsRow.id = `items-${order.id}`;
            itemsRow.className = 'd-none'; // Bootstrap display none
            itemsRow.innerHTML = `<td colspan="4" class="p-0 bg-dark bg-opacity-25 border-bottom-0">${itemsHtml}</td>`;
            
            tbody.appendChild(itemsRow);
        });

        // Update Grand Total
        grandTotalEl.innerText = grandTotal.toFixed(2);
    }

    // Toggle Items View
    window.toggleItems = (orderId) => {
        const itemsRow = document.getElementById(`items-${orderId}`);
        const iconBtn = document.getElementById(`icon-${orderId}`);
        
        if (itemsRow.classList.contains('d-none')) {
            itemsRow.classList.remove('d-none');
            iconBtn.innerText = '-';
        } else {
            itemsRow.classList.add('d-none');
            iconBtn.innerText = '+';
        }
    };

    // Cancel Order
    window.cancelOrder = (orderId) => {
        if (!confirm('Are you sure you want to cancel this order?')) return;
        fetch(`/api/orders/${orderId}/cancel`, {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Order canceled successfully!');
                fetchOrders(); // Refresh table
            } else {
                alert(data.message || 'Failed to cancel order.');
            }
        });
    };

    // 5. Setup Listeners
    filterBtn.addEventListener('click', fetchOrders);

    // Initial Fetch
    fetchOrders();
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>