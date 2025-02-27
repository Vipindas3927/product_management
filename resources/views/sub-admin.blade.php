<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sub Admins') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="float-end">
                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Add</button>
                    </div>
                    <div class="responsible-table">
                        <table class="table table-dark" id="userTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Added On</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ $loop->iteration + $users->firstItem() - 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" autocomplete="off" type="checkbox" role="switch" id="flexSwitchCheckDefault-{{ $user->id }}" data-id="{{ $user->id }}" {{ $user->status ? 'checked' : '' }}>
                                                <label class="form-check-label @if( $user->status) text-success @else text-danger @endif" for="flexSwitchCheckDefault-{{ $user->id }}" id="user-status-{{ $user->id }}"> {{ $user->status ? 'Active' : 'Deactivated' }}</label>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6">
                                            No data found...
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>


<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="successMessage" class="alert alert-success d-none"></div>
                <div id="errorAlert" class="alert alert-danger d-none"></div>
                <form id="addUserForm">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control bg-dark text-white" id="name" name="name">
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control bg-dark text-white" id="email" name="email">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control bg-dark text-white" id="password" name="password">
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>

                    <button type="submit" class="btn btn-success">Add User</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        $('#addUserForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            $('.invalid-feedback').text('').hide();
            $('#errorAlert').text('').addClass('d-none');
            $('#successMessage').text('').addClass('d-none')

            $.ajax({
                url: "{{ route('sub-admin.add') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log(response);
                    $('#successMessage').text(response['success']).removeClass('d-none');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                },
                error: function (xhr) {
                    let errors = xhr.responseJSON.errors;
                    if (errors) {
                        if (errors.name) $('#nameError').text(errors.name[0]).show();
                        if (errors.email) $('#emailError').text(errors.email[0]).show();
                        if (errors.password) $('#passwordError').text(errors.password[0]).show();
                    }
                    if (xhr.responseJSON.error) {
                        $('#errorAlert').text(xhr.responseJSON.error).removeClass('d-none');
                    }
                }
            });
        });

        $(document).on('change', '.toggle-status', function () {
            let userId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            let label = $("#user-status-" + userId);

            $.ajax({
                url: "{{ route('sub-admin.toggle.status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: userId,
                },
                success: function (response) {
                    label.removeClass("text-success text-danger")
                        .addClass(status ? "text-success" : "text-danger")
                        .text(status ? "Active" : "Deactivated");
                },
                error: function () {
                    console.log("Failed to update status. Please try again.");
                }
            });
        });

    });
</script>
@endpush
</x-app-layout>