@extends('layouts.app')

@section('title', 'Production Schedules')
@section('page-title', 'Production Schedules')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[360px_1fr]">
        <form id="schedule-form" class="h-fit rounded-xl border bg-white p-5 shadow-sm">
            <h3 class="font-semibold">Create Schedule</h3>

            <label class="mt-5 block text-sm font-medium">Sewing Line</label>
            <select name="sewing_line_id" id="sewing-line-select" required
                class="mt-1 w-full rounded-lg border bg-white px-3 py-2.5">
                <option value="">Loading lines...</option>
            </select>

            <label class="mt-4 block text-sm font-medium">Production Order</label>
            <select name="production_order_id" id="order-select" required
                class="mt-1 w-full rounded-lg border bg-white px-3 py-2.5">
                <option value="">Loading orders...</option>
            </select>

            <label class="mt-4 block text-sm font-medium">Start Sewing</label>
            <input name="start_date" type="date" required class="mt-1 w-full rounded-lg border px-3 py-2.5">

            <label class="mt-4 block text-sm font-medium">Finish Sewing</label>
            <input name="finish_date" type="date" required class="mt-1 w-full rounded-lg border px-3 py-2.5">

            <label class="mt-4 block text-sm font-medium">Total Target (pcs)</label>
            <input name="total_target" type="number" min="1" required
                class="mt-1 w-full rounded-lg border px-3 py-2.5">

            <p id="schedule-error" class="mt-3 hidden text-sm text-red-600"></p>

            <button id="schedule-submit" type="submit"
                class="mt-5 w-full rounded-lg bg-blue-700 px-4 py-3
                       font-semibold text-white hover:bg-blue-800">
                Create Schedule
            </button>
        </form>

        <section class="min-w-0 rounded-xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold">Schedule List</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Klik detail untuk melihat target dan input aktual.
                    </p>
                </div>
                <button id="schedule-refresh" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50">
                    Refresh
                </button>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="p-3">Order / Style</th>
                            <th class="p-3">Line</th>
                            <th class="p-3">Start</th>
                            <th class="p-3">Finish</th>
                            <th class="p-3">Target</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody id="schedule-table">
                        <tr>
                            <td colspan="7" class="p-4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Detail schedule modal -->
    <div id="schedule-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4">
        <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 id="modal-title" class="text-lg font-bold">Schedule Detail</h3>
                    <p id="modal-subtitle" class="mt-1 text-sm text-slate-500"></p>
                </div>
                <button id="modal-close" class="rounded-lg border px-3 py-2 hover:bg-slate-50">
                    Close
                </button>
            </div>

            <div id="modal-loading" class="py-8 text-center text-slate-500">
                Loading detail...
            </div>

            <div id="daily-content" class="mt-5 hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="p-3">Date</th>
                                <th class="p-3">Base Target</th>
                                <th class="p-3">Target</th>
                                <th class="p-3">Actual</th>
                                <th class="p-3">Shortage</th>
                                <th class="p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody id="daily-table"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Input actual modal -->
    <div id="actual-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/50 p-4">
        <form id="actual-form" class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold">Input Actual Output</h3>
            <p id="actual-date-label" class="mt-1 text-sm text-slate-500"></p>

            <label class="mt-5 block text-sm font-medium">Actual Output (pcs)</label>
            <input id="actual-qty" name="actual_qty" type="number" min="0" required
                class="mt-1 w-full rounded-lg border px-3 py-2.5">

            <p id="actual-error" class="mt-3 hidden text-sm text-red-600"></p>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" id="actual-cancel" class="rounded-lg border px-4 py-2.5">
                    Cancel
                </button>
                <button id="actual-submit" type="submit"
                    class="rounded-lg bg-blue-700 px-4 py-2.5 font-semibold text-white">
                    Save Actual
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const scheduleForm = document.getElementById('schedule-form');
        const scheduleTable = document.getElementById('schedule-table');
        const scheduleSubmit = document.getElementById('schedule-submit');
        const scheduleError = document.getElementById('schedule-error');
        const lineSelect = document.getElementById('sewing-line-select');
        const orderSelect = document.getElementById('order-select');

        const scheduleModal = document.getElementById('schedule-modal');
        const actualModal = document.getElementById('actual-modal');
        const dailyTable = document.getElementById('daily-table');
        const dailyContent = document.getElementById('daily-content');
        const modalLoading = document.getElementById('modal-loading');
        const actualForm = document.getElementById('actual-form');
        const actualQtyInput = document.getElementById('actual-qty');
        const actualError = document.getElementById('actual-error');
        const actualSubmit = document.getElementById('actual-submit');

        let currentScheduleId = null;
        let currentDailyProductionId = null;

        async function scheduleApi(url, options = {}) {
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

        function displayError(element, error) {
            const errors = error.data?.errors || {};
            element.textContent = Object.values(errors).flat().join(' ') ||
                error.message;
            element.classList.remove('hidden');
        }

        function statusBadge(status) {
            const styles = {
                planned: 'bg-slate-100 text-slate-700',
                in_progress: 'bg-blue-100 text-blue-700',
                delayed: 'bg-red-100 text-red-700',
                completed: 'bg-green-100 text-green-700'
            };

            const badge = document.createElement('span');
            badge.className = 'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ' +
                (styles[status] || styles.planned);
            badge.textContent = (status || 'planned').replaceAll('_', ' ');
            return badge;
        }

        function formatNumber(value) {
            return Number(value || 0).toLocaleString('id-ID');
        }

        async function loadMasterOptions() {
            try {
                const [lineResult, orderResult] = await Promise.all([
                    scheduleApi('/api/sewing-lines'),
                    scheduleApi('/api/orders')
                ]);

                lineSelect.replaceChildren();
                const linePlaceholder = new Option('Select sewing line', '');
                lineSelect.add(linePlaceholder);

                lineResult.data
                    .filter(line => line.is_active)
                    .forEach(line => {
                        lineSelect.add(new Option(
                            `${line.code} - ${line.name}`,
                            line.id
                        ));
                    });

                orderSelect.replaceChildren();
                orderSelect.add(new Option('Select production order', ''));

                orderResult.data
                    .filter(order => order.is_active)
                    .forEach(order => {
                        orderSelect.add(new Option(
                            `${order.order_number} - ${order.style}`,
                            order.id
                        ));
                    });
            } catch (error) {
                notify('Gagal memuat master line/order: ' + error.message, false);
            }
        }

        async function loadSchedules() {
            scheduleTable.innerHTML =
                '<tr><td colspan="7" class="p-4">Loading schedules...</td></tr>';

            try {
                const result = await scheduleApi('/schedules');
                scheduleTable.replaceChildren();

                if (!result.data.length) {
                    scheduleTable.innerHTML =
                        '<tr><td colspan="7" class="p-4 text-slate-500">Belum ada schedule.</td></tr>';
                    return;
                }

                result.data.forEach(schedule => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-t';

                    const cells = [
                        `${schedule.production_order?.order_number || '-'} / ${schedule.production_order?.style || '-'}`,
                        schedule.sewing_line?.code || '-',
                        String(schedule.start_date).slice(0, 10),
                        String(schedule.finish_date).slice(0, 10),
                        formatNumber(schedule.total_target)
                    ];

                    cells.forEach(value => {
                        const td = document.createElement('td');
                        td.className = 'p-3 whitespace-nowrap';
                        td.textContent = value;
                        tr.appendChild(td);
                    });

                    const statusCell = document.createElement('td');
                    statusCell.className = 'p-3';
                    statusCell.appendChild(statusBadge(schedule.status));
                    tr.appendChild(statusCell);

                    const actionCell = document.createElement('td');
                    actionCell.className = 'p-3';

                    const detailButton = document.createElement('button');
                    detailButton.type = 'button';
                    detailButton.className =
                        'rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100';
                    detailButton.textContent = 'Detail / Actual';
                    detailButton.addEventListener('click', () => openSchedule(schedule.id));

                    actionCell.appendChild(detailButton);
                    tr.appendChild(actionCell);
                    scheduleTable.appendChild(tr);
                });
            } catch (error) {
                scheduleTable.innerHTML =
                    '<tr><td colspan="7" class="p-4 text-red-600">Gagal memuat schedule.</td></tr>';
                notify(error.message, false);
            }
        }

        scheduleForm.addEventListener('submit', async event => {
            event.preventDefault();
            scheduleSubmit.disabled = true;
            scheduleSubmit.textContent = 'Saving...';
            scheduleError.classList.add('hidden');

            const formData = new FormData(scheduleForm);
            const payload = Object.fromEntries(formData.entries());
            payload.sewing_line_id = Number(payload.sewing_line_id);
            payload.production_order_id = Number(payload.production_order_id);
            payload.total_target = Number(payload.total_target);

            try {
                const result = await scheduleApi('/schedules', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                scheduleForm.reset();
                notify(result.message);
                await loadSchedules();

                // Buka detail schedule baru agar target harian langsung terlihat.
                await openSchedule(result.data.id);
            } catch (error) {
                displayError(scheduleError, error);
                notify(error.message, false);
            } finally {
                scheduleSubmit.disabled = false;
                scheduleSubmit.textContent = 'Create Schedule';
            }
        });

        async function openSchedule(scheduleId) {
            currentScheduleId = scheduleId;
            scheduleModal.classList.remove('hidden');
            scheduleModal.classList.add('flex');
            dailyContent.classList.add('hidden');
            modalLoading.classList.remove('hidden');
            modalLoading.textContent = 'Loading detail...';

            try {
                const result = await scheduleApi(`/schedules/${scheduleId}`);
                const schedule = result.data;

                document.getElementById('modal-title').textContent =
                    `${schedule.production_order?.order_number || 'Order'} — ${schedule.production_order?.style || ''}`;

                document.getElementById('modal-subtitle').textContent =
                    `${schedule.sewing_line?.name || schedule.sewing_line?.code || 'Line'} · ${schedule.start_date} – ${schedule.finish_date}`;

                renderDailyRows(schedule.daily_productions || []);
                modalLoading.classList.add('hidden');
                dailyContent.classList.remove('hidden');
            } catch (error) {
                modalLoading.textContent = 'Gagal memuat detail: ' + error.message;
                notify(error.message, false);
            }
        }

        function renderDailyRows(days) {
            dailyTable.replaceChildren();

            if (!days.length) {
                dailyTable.innerHTML =
                    '<tr><td colspan="6" class="p-4">Belum ada detail produksi.</td></tr>';
                return;
            }

            days.forEach(day => {
                const tr = document.createElement('tr');
                tr.className = 'border-t';

                const actual = Number(day.actual_qty || 0);
                const target = Number(day.target_qty || 0);
                const shortage = Math.max(0, target - actual);

                [
                    String(day.production_date).slice(0, 10),
                    formatNumber(day.base_target_qty),
                    formatNumber(target),
                    formatNumber(actual),
                    formatNumber(shortage)
                ].forEach(value => {
                    const td = document.createElement('td');
                    td.className = 'p-3 whitespace-nowrap';
                    td.textContent = value;
                    tr.appendChild(td);
                });

                const actionCell = document.createElement('td');
                actionCell.className = 'p-3';

                const button = document.createElement('button');
                button.type = 'button';
                button.className =
                    'rounded-lg bg-blue-700 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-800';
                button.textContent = 'Input Actual';
                button.addEventListener('click', () => openActualModal(day));

                actionCell.appendChild(button);
                tr.appendChild(actionCell);
                dailyTable.appendChild(tr);
            });
        }

        function openActualModal(day) {
            currentDailyProductionId = day.id;
            actualForm.reset();
            actualError.classList.add('hidden');

            document.getElementById('actual-date-label').textContent =
                `Production date: ${String(day.production_date).slice(0, 10)}`;

            actualQtyInput.value = Number(day.actual_qty || 0);
            actualModal.classList.remove('hidden');
            actualModal.classList.add('flex');
            actualQtyInput.focus();
        }

        async function closeActualModal() {
            actualModal.classList.add('hidden');
            actualModal.classList.remove('flex');
        }

        actualForm.addEventListener('submit', async event => {
            event.preventDefault();
            actualSubmit.disabled = true;
            actualSubmit.textContent = 'Saving...';
            actualError.classList.add('hidden');

            const actualQty = Number(actualQtyInput.value);

            if (!Number.isInteger(actualQty) || actualQty < 0) {
                actualError.textContent = 'Aktual harus berupa bilangan bulat nol atau lebih.';
                actualError.classList.remove('hidden');
                actualSubmit.disabled = false;
                actualSubmit.textContent = 'Save Actual';
                return;
            }

            try {
                const startedAt = performance.now();

                console.time('Save actual output');

                try {
                    const result = await scheduleApi(
                        `/daily-productions/${currentDailyProductionId}/actual`, {
                            method: 'POST',
                            body: JSON.stringify({
                                actual_qty: actualQty
                            })
                        }
                    );

                    console.log(
                        'Waktu response server:',
                        ((performance.now() - startedAt) / 1000).toFixed(2),
                        'detik'
                    );

                    console.timeEnd('Save actual output');

                    await closeActualModal();
                    notify(result.message);

                    const refreshStartedAt = performance.now();

                    await loadSchedules();
                    await openSchedule(currentScheduleId);

                    console.log(
                        'Waktu refresh tampilan:',
                        ((performance.now() - refreshStartedAt) / 1000).toFixed(2),
                        'detik'
                    );
                } catch (error) {
                    displayError(actualError, error);
                    notify(error.message, false);
                } finally {
                    actualSubmit.disabled = false;
                    actualSubmit.textContent = 'Save Actual';
                }

                await closeActualModal();
                notify(result.message);

                // Refresh daftar dan detail untuk menampilkan balancing terbaru.
                await loadSchedules();
                await openSchedule(currentScheduleId);
            } catch (error) {
                displayError(actualError, error);
                notify(error.message, false);
            } finally {
                actualSubmit.disabled = false;
                actualSubmit.textContent = 'Save Actual';
            }
        });

        document.getElementById('modal-close').addEventListener('click', () => {
            scheduleModal.classList.add('hidden');
            scheduleModal.classList.remove('flex');
        });

        document.getElementById('actual-cancel').addEventListener('click', closeActualModal);
        document.getElementById('schedule-refresh').addEventListener('click', loadSchedules);

        loadMasterOptions();
        loadSchedules();
    </script>
@endpush
