@extends('layouts.app')

@section('title', 'Sewing Lines')
@section('page-title', 'Master Sewing Lines')

@section('content')
<div class="grid gap-6 xl:grid-cols-[360px_1fr]">
    <form id="line-form" class="h-fit rounded-xl border bg-white p-5 shadow-sm">
        <h3 class="font-semibold">Add Sewing Line</h3>

        <label class="mt-5 block text-sm font-medium">Line Code</label>
        <input name="code" required maxlength="30"
               placeholder="LINE-01"
               class="mt-1 w-full rounded-lg border px-3 py-2.5">

        <label class="mt-4 block text-sm font-medium">Line Name</label>
        <input name="name" required maxlength="100"
               placeholder="Sewing Line 1"
               class="mt-1 w-full rounded-lg border px-3 py-2.5">

        <p id="line-error" class="mt-3 hidden text-sm text-red-600"></p>

        <button id="line-submit" type="submit"
                class="mt-5 w-full rounded-lg bg-blue-700 px-4 py-3
                       font-semibold text-white hover:bg-blue-800">
            Save Sewing Line
        </button>
    </form>

    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Sewing Line List</h3>
            <button id="line-refresh"
                    class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50">
                Refresh
            </button>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="p-3">Code</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody id="line-table">
                    <tr><td colspan="3" class="p-4 text-slate-500">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const lineTable = document.getElementById('line-table');
const lineForm = document.getElementById('line-form');
const lineSubmit = document.getElementById('line-submit');
const lineError = document.getElementById('line-error');

/*function notify(message, success = true) {
    const box = document.getElementById('notification');
    box.textContent = message;
    box.className = 'mb-5 rounded-lg border px-4 py-3 text-sm ' +
        (success
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800');
    box.classList.remove('hidden');
}*/

async function api(url, options = {}) {
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

function showLineError(error) {
    const errors = error.data?.errors || {};
    lineError.textContent = Object.values(errors).flat().join(' ');
    if (!lineError.textContent) lineError.textContent = error.message;
    lineError.classList.remove('hidden');
}

async function loadLines() {
    lineTable.innerHTML =
        '<tr><td colspan="3" class="p-4">Loading data...</td></tr>';

    try {
        const result = await api('/api/sewing-lines');
        lineTable.replaceChildren();

        if (!result.data.length) {
            lineTable.innerHTML =
                '<tr><td colspan="3" class="p-4 text-slate-500">Belum ada data.</td></tr>';
            return;
        }

        result.data.forEach(line => {
            const tr = document.createElement('tr');
            tr.className = 'border-t';
            [line.code, line.name, line.is_active ? 'Active' : 'Inactive']
                .forEach(value => {
                    const td = document.createElement('td');
                    td.className = 'p-3';
                    td.textContent = value;
                    tr.appendChild(td);
                });
            lineTable.appendChild(tr);
        });
    } catch (error) {
        lineTable.innerHTML =
            '<tr><td colspan="3" class="p-4 text-red-600">Gagal memuat data.</td></tr>';
        notify(error.message, false);
    }
}

lineForm.addEventListener('submit', async event => {
    event.preventDefault();
    lineSubmit.disabled = true;
    lineSubmit.textContent = 'Saving...';
    lineError.classList.add('hidden');

    const formData = new FormData(lineForm);
    const payload = Object.fromEntries(formData.entries());

    try {
        const result = await api('/api/sewing-lines', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        lineForm.reset();
        notify(result.message);
        await loadLines();
    } catch (error) {
        showLineError(error);
        notify(error.message, false);
    } finally {
        lineSubmit.disabled = false;
        lineSubmit.textContent = 'Save Sewing Line';
    }
});

document.getElementById('line-refresh').addEventListener('click', loadLines);
loadLines();
</script>
@endpush