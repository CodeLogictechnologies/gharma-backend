@extends('layouts.main')
@section('title', 'Role Permissions')

@section('content')

{{-- ── Save confirmation modal ──────────────────────────────────── --}}
<div class="modal fade" id="saveModal" tabindex="-1" data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Permissions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to save permissions for this role?</div>
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
            <h5 class="mb-0">Role Permissions</h5>
        </div>
        <div class="card-body pt-3">

            {{-- ── Role selector row ─────────────────────────────── --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <label class="form-label mb-0 fw-semibold">Role</label>
                <select class="form-select" id="roleSelect" style="max-width: 220px;">
                    <option value="">-- Select Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
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
                                <th>Module Name</th>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input col-check" data-col="add"> Add
                                </th>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input col-check" data-col="edit"> Edit
                                </th>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input col-check" data-col="view"> View
                                </th>
                                <th class="text-center">
                                    <input type="checkbox" class="form-check-input col-check" data-col="delete"> Delete
                                </th>
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

            <div id="noRoleMsg" class="text-muted mt-2" style="display:none;">
                Please select a role and click View.
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── Module definitions ──────────────────────────────────────────
    // Each module maps to permission name patterns: module.add, module.edit, module.view, module.delete
    // Adjust these to match your actual permission names in the DB
    var modules = [
        { label: 'Dashboard',    key: 'dashboard',    indent: 0 },
        { label: 'School Setup', key: 'school-setup', indent: 0 },
        { label: 'Academic',     key: 'academic',     indent: 0 },
        { label: 'Class Setup',  key: 'class-setup',  indent: 1 },
        { label: 'Section',      key: 'section',      indent: 1 },
        // ── Add more modules here ──
    ];

    var allPermissions  = @json($permissions);   // full permission list from controller
    var rolePermissions = [];                     // IDs assigned to selected role

    // ── View button ─────────────────────────────────────────────────
    $('#viewBtn').on('click', function () {
        var roleId = $('#roleSelect').val();
        if (!roleId) {
            $('#permissionTableWrap').hide();
            $('#noRoleMsg').show();
            return;
        }

        $.post('{{ route("role.getPermissions") }}', { id: roleId }, function (res) {
            var result = typeof res === 'string' ? JSON.parse(res) : res;
            rolePermissions = result.permissions || [];
            buildMatrix();
            $('#permissionTableWrap').show();
            $('#noRoleMsg').hide();
        });
    });

    // ── Build the permission matrix rows ────────────────────────────
    function buildMatrix() {
        var $tbody = $('#permissionBody').empty();

        modules.forEach(function (mod) {
            var paddingLeft = mod.indent === 1 ? '2rem' : (mod.indent === 2 ? '3.5rem' : '0');
            var collapseBtn = mod.indent === 0
                ? '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 collapse-row me-1" style="font-size:11px; line-height:1.4;">−</button>'
                : '';

            var row = '<tr data-key="' + mod.key + '" data-indent="' + mod.indent + '">';
            row    += '<td>' + collapseBtn + '<input type="checkbox" class="form-check-input row-select" data-key="' + mod.key + '"></td>';
            row    += '<td style="padding-left:' + paddingLeft + ';">' + mod.label + '</td>';

            ['add','edit','view','delete'].forEach(function (action) {
                var permName = mod.key + '.' + action;
                var perm     = allPermissions.find(function (p) { return p.name === permName; });
                if (perm) {
                    var checked = rolePermissions.indexOf(perm.id) !== -1 ? 'checked' : '';
                    row += '<td class="text-center"><input type="checkbox" class="form-check-input perm-check" '
                         + 'data-perm-id="' + perm.id + '" data-key="' + mod.key + '" data-action="' + action + '" ' + checked + '></td>';
                } else {
                    row += '<td class="text-center"><span class="text-muted">–</span></td>';
                }
            });

            row += '</tr>';
            $tbody.append(row);
        });
    }

    // ── Select All header checkbox ───────────────────────────────────
    $('#selectAll').on('change', function () {
        var checked = $(this).is(':checked');
        $('.perm-check').prop('checked', checked);
        $('.row-select').prop('checked', checked);
    });

    // ── Column header checkboxes (Add / Edit / View / Delete) ────────
    $(document).on('change', '.col-check', function () {
        var col     = $(this).data('col');
        var checked = $(this).is(':checked');
        $('.perm-check[data-action="' + col + '"]').prop('checked', checked);
    });

    // ── Row select checkbox (checks all 4 actions in that row) ───────
    $(document).on('change', '.row-select', function () {
        var key     = $(this).data('key');
        var checked = $(this).is(':checked');
        $('.perm-check[data-key="' + key + '"]').prop('checked', checked);
    });

    // ── Collapse/expand child rows ───────────────────────────────────
    $(document).on('click', '.collapse-row', function () {
        var $btn    = $(this);
        var $row    = $btn.closest('tr');
        var key     = $row.data('key');
        var isOpen  = $btn.text() === '−';

        // Find sibling rows that come after this parent (indent > 0) until next indent-0
        var $siblings = $row.nextAll('tr');
        var $children = $();
        $siblings.each(function () {
            if ($(this).data('indent') > 0) { $children = $children.add($(this)); }
            else { return false; }
        });

        if (isOpen) {
            $children.hide();
            $btn.text('+');
        } else {
            $children.show();
            $btn.text('−');
        }
    });

    // ── Save button → confirm modal ──────────────────────────────────
    $('#savePermissionsBtn').on('click', function () {
        new bootstrap.Modal(document.getElementById('saveModal')).show();
    });

    $('#confirmSave').on('click', function () {
        var roleId      = $('#roleSelect').val();
        var permIds     = [];

        $('.perm-check:checked').each(function () {
            permIds.push($(this).data('perm-id'));
        });

        $.post('{{ route("role.save") }}', {
            id:          roleId,
            permissions: permIds,
            _token:      '{{ csrf_token() }}'
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
@endpush