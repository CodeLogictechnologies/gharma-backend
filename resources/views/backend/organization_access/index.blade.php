@extends('layouts.main')
@section('title', 'Organization Access')

@section('content')

{{-- ── Save confirmation modal ──────────────────────────────────── --}}
<div class="modal fade" id="saveModal" tabindex="-1" data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to save permissions for this organization?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSave">Yes, Save</button>
            </div>
        </div>
    </div>
</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Organization Access</h5>
        </div>
        <div class="card-body pt-3">

            {{-- ── Organization selector row ─────────────────────── --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <label class="form-label mb-0 fw-semibold">Organization</label>
                <select class="form-select" id="orgSelect" style="max-width: 280px;">
                    <option value="">-- Select Organization --</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-primary" id="viewBtn">View</button>
            </div>

            {{-- ── Permission matrix ─────────────────────────────── --}}
            <div id="permissionTableWrap" style="display:none;">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="permissionMatrix">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll" title="Select All">
                                </th>
                                <th>Sidebar Name</th>
                                <th class="text-center">Add</th>
                                <th class="text-center">Edit</th>
                                <th class="text-center">View</th>
                                <th class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="permissionBody">
                            {{-- Populated by JS --}}
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="savePermissionsBtn">
                        <i class="fa fa-save me-1"></i> Save Permissions
                    </button>
                </div>
            </div>

            <div id="noOrgMsg" class="text-muted mt-2" style="display:none;">
                Please select an organization and click View.
            </div>

        </div>
    </div>
</div>

@endsection

@section('main-scripts')
<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // ── Module definitions (same as role permissions) ───────────────
        var modules = [
            { label: 'Favicon',       key: 'favicon' },
            { label: 'Role',          key: 'role' },
            { label: 'Home Tab',      key: 'hometab' },
            { label: 'Store',         key: 'store' },
            { label: 'Category',      key: 'category' },
            { label: 'Brand',         key: 'brand' },
            { label: 'Item',          key: 'item' },
            { label: 'Users',         key: 'user' },
            { label: 'Driver List',   key: 'driverlist' },
            { label: 'Inventory',     key: 'inventory' },
            { label: 'Vendor',        key: 'vendor' },
            { label: 'Retailer',      key: 'retailer' },
            { label: 'Wholesaler',    key: 'wholesaler' },
            { label: 'Discount',      key: 'discount' },
            { label: 'Coupon',        key: 'coupon' },
            { label: 'Loyalty',       key: 'loyalty' },
            { label: 'Order',         key: 'order' },
            { label: 'Invoice',       key: 'invoice' },
            { label: 'Refund',        key: 'refund' },
            { label: 'Report',        key: 'report' },
            { label: 'Heatmap',       key: 'heatmap' },
            { label: 'Notification',  key: 'notification' },
        ];

        var allPermissions  = @json($permissions);
        var orgPermissions  = []; // permission IDs assigned to selected org

        // ── View button ─────────────────────────────────────────────────
        $('#viewBtn').on('click', function () {
            var orgId = $('#orgSelect').val();
            if (!orgId) {
                $('#permissionTableWrap').hide();
                $('#noOrgMsg').show();
                return;
            }

            $.post('{{ route("organization.access.getPermissions") }}', { id: orgId }, function (res) {
                var result = typeof res === 'string' ? JSON.parse(res) : res;
                orgPermissions = result.permissions || [];
                buildMatrix();
                $('#permissionTableWrap').show();
                $('#noOrgMsg').hide();
            });
        });

        // ── Build the permission matrix rows ────────────────────────────
        function buildMatrix() {
            var $tbody = $('#permissionBody').empty();

            modules.forEach(function (mod) {
                var row = '<tr data-key="' + mod.key + '">';
                row += '<td><input type="checkbox" class="form-check-input row-select" data-key="' + mod.key + '"></td>';
                row += '<td>' + mod.label + '</td>';

                ['add', 'edit', 'view', 'delete'].forEach(function (action) {
                    var permName = mod.key + '.' + action;
                    var perm     = allPermissions.find(function (p) { return p.name === permName; });
                    var permId   = perm ? perm.id : null;
                    var checked  = perm && orgPermissions.indexOf(perm.id) !== -1 ? 'checked' : '';

                    row += '<td class="text-center"><input type="checkbox" class="form-check-input perm-check" ' +
                        'data-perm-id="' + permId + '" data-key="' + mod.key + '" data-action="' + action + '" ' + checked + '></td>';
                });

                row += '</tr>';
                $tbody.append(row);
            });
        }

        // ── Select All ──────────────────────────────────────────────────
        $('#selectAll').on('change', function () {
            var checked = $(this).is(':checked');
            $('.perm-check').prop('checked', checked);
            $('.row-select').prop('checked', checked);
        });

        // ── Row select ──────────────────────────────────────────────────
        $(document).on('change', '.row-select', function () {
            var key     = $(this).data('key');
            var checked = $(this).is(':checked');
            $('.perm-check[data-key="' + key + '"]').prop('checked', checked);
        });

        // ── Save button → confirm modal ──────────────────────────────────
        $('#savePermissionsBtn').on('click', function () {
            new bootstrap.Modal(document.getElementById('saveModal')).show();
        });

        $('#confirmSave').on('click', function () {
            var orgId     = $('#orgSelect').val();
            var permNames = [];

            $('.perm-check:checked').each(function () {
                var key    = $(this).data('key');
                var action = $(this).data('action');
                permNames.push(key + '.' + action);
            });

            $.post('{{ route("organization.access.save-permissions") }}', {
                id:         orgId,
                perm_names: permNames,
                _token:     '{{ csrf_token() }}'
            }, function (res) {
                var result = typeof res === 'string' ? JSON.parse(res) : res;
                showNotification(result.message, result.type);
                bootstrap.Modal.getInstance(document.getElementById('saveModal')).hide();
            }).fail(function () {
                showNotification('Something went wrong!', 'error');
            });
        });

    });
</script>
@endsection