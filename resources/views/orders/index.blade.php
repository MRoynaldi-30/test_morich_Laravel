@extends('layouts.app')

@section('title', 'Master Orders')
@section('page-title', 'Master Orders')

@section('content')
<div class="grid gap-6 xl:grid-cols-[360px_1fr]">
    <form id="order-form" class="h-fit rounded-xl border bg-white p-5 shadow-sm">
        <h3 class="font-semibold">Add Production Order</h3>

        <label class="mt-5 block text-sm font-medium">Order Number</label>
        <input name="order_number" required maxlength="50"
               placeholder="ORD-001"
               class="mt-1 w-full rounded-lg border px-3 py-2.5">

        <label class="mt-4 block text-sm font-medium">Style</label>
        <input name="style" required maxlength="100"
               placeholder="STYLE-A"
               class="mt-1 w-full rounded-lg border px-3 py-2.5">

        <label class="mt-4 block text-sm font-medium">Description</label>
        <textarea name="description" maxlength="255"
                  class="mt-1 w-full rounded-lg border px-3 py-2.5"></textarea>

        <label class="mt-4 block text-sm font-medium">Total Order Qty</label>
        <input name="total_order_qty" type="number" min="1" required
               class="mt-1 w-full rounded-lg border px-3 py-2.5">

        <p id="order-error" class="mt-3 hidden text-sm text-red-600"></p>

        <button id="order-submit" type="submit"
                class="mt-5 w-full rounded-lg bg-blue-700 px-4 py-3
                       font-semibold text-white hover:bg-blue-800">
            Save Order
        </button>
    </form>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Production Order List</h3>
            <button id="order-refresh"
                    class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50">
                Refresh
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3">Order</th>
                        <th class="p-3">Style</th>
                        <th class="p-3">Total Qty</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody id="order-table">
                    <tr><td colspan="4" class="p-4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const orderTable = document.getElementById('order-table');
const orderForm = document.getElementById('order-form');
const orderSubmit = document.getElementById('order-submit');
const orderError = document.getElementById('order-error');

async function orderApi(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector(
                'meta[name="csrf-token"]'
            ).content,
            ...(options.headers || {})
        }
    });

    const result = await response.json();

    if (!response.ok) {
        const error = new Error(result.message || 'Request gagal.');
        error.data = result;
        throw error;
    }

    return result;
}

async function loadOrders() {
    orderTable.innerHTML =
        '<tr><td colspan="4" class="p-4">Loading data...</td></tr>';

    try {
        const result = await orderApi('/api/orders');
        orderTable.replaceChildren();

        if (!result.data.length) {
            orderTable.innerHTML =
                '<tr><td colspan="4" class="p-4 text-slate-500">Belum ada order.</td></tr>';
            return;
        }

        result.data.forEach(order => {
            const tr = document.createElement('tr');
            tr.className = 'border-t';

            [
                order.order_number,
                order.style,
                Number(order.total_order_qty).toLocaleString('id-ID'),
                order.is_active ? 'Active' : 'Inactive'
            ].forEach(value => {
                const td = document.createElement('td');
                td.className = 'p-3';
                td.textContent = value;
                tr.appendChild(td);
            });

            orderTable.appendChild(tr);
        });
    } catch (error) {
        orderTable.innerHTML =
            '<tr><td colspan="4" class="p-4 text-red-600">Gagal memuat data.</td></tr>';
        notify(error.message, false);
    }
}

orderForm.addEventListener('submit', async event => {
    event.preventDefault();
    orderSubmit.disabled = true;
    orderSubmit.textContent = 'Saving...';
    orderError.classList.add('hidden');

    const formData = new FormData(orderForm);
    const payload = Object.fromEntries(formData.entries());
    payload.total_order_qty = Number(payload.total_order_qty);

    try {
        const result = await orderApi('/api/orders', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        orderForm.reset();
        notify(result.message);
        await loadOrders();
    } catch (error) {
        const errors = error.data?.errors || {};
        orderError.textContent = Object.values(errors).flat().join(' ')
            || error.message;
        orderError.classList.remove('hidden');
        notify(error.message, false);
    } finally {
        orderSubmit.disabled = false;
        orderSubmit.textContent = 'Save Order';
    }
});

document.getElementById('order-refresh').addEventListener('click', loadOrders);
loadOrders();
</script>
@endpush