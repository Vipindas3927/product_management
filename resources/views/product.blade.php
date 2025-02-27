<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Products') }}
        </h2>
    </x-slot>
   
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="bulkDeleteContainer" class="d-none mt-3">
                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <span><strong>Warning:</strong> You have selected <span id="selectedCount">0</span> products. Do you want to delete them?</span>
                            <div>
                                <button type="button" class="btn btn-secondary btn-sm" id="cancelBulkDelete">Cancel</button>
                                <button type="button" id="confirmBulkDelete" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger d-flex justify-content-between align-items-center d-none" id="error"></div>
                    <div class="float-end">
                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Add Product</button>
                    </div>
                    <form>
                        <div class="row pb-3">
                            <div class="me-2 col-2">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" value="{{ request('name') }}" class="form-control bg-dark text-white">
                                </div>
                            </div>
                            <div class="me-2 col-2">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select name="status" class="form-control bg-dark text-white" id="status">
                                        <option value="">All</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Out of stock</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Available</option>
                                    </select>
                                </div>
                            </div>
                            <div class="me-2 col-2">
                                <div class="form-group">
                                    <label for="from">From Date</label>
                                    <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control bg-dark text-white">
                                </div>
                            </div>
                            <div class="me-2 col-2">
                                <div class="form-group">
                                    <label for="to">To Date</label>
                                    <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control bg-dark text-white">
                                </div>
                            </div>
                            <div class="me-2 col-2"><br>
                                <div class="form-group me-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('product') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="responsible-table">
                        <table class="table table-dark" id="productTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-control" id="checkAll" autocomplete="off">
                                    </th>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Quantity</th>
                                    <th>Added By</th>
                                    <th>Images</th>
                                    <th>Added On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="productCheckbox fprm-control" value="{{ $product->id }}" autocomplete="off">
                                        </td>
                                        <td>{{ $loop->iteration + $products->firstItem() - 1 }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->code }}</td>
                                        <td>{{ $product->quantity }}</td>
                                        <td>{{ $product->addBy->name }}</td>
                                        <td>
                                            @if($product->images->count() > 0)
                                            <div id="carousel{{ $product->id }}" class="carousel slide" data-bs-ride="carousel" style="width:100px;">
                                                <div class="carousel-inner">
                                                    @foreach($product->images as $key => $image)
                                                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $image->image) }}" class="d-block w-100 rounded"
                                                                 style="max-width: 100px; max-height: 100px; object-fit: cover;" 
                                                                 alt="{{ $product->name }} Image">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @else
                                                No images
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($product->created_at)->format('d M Y h:i A') }}</td>
                                        <td>
                                            <div class="me-2">
                                                <button class="btn btn-sm btn-primary edit-button" data-id="{{ $product->id }}">Edit</button>
                                                <button type="button" class="btn btn-danger btn-sm delete-product" data-id="{{ $product->id }}">Delete</button>
                                            </div>
                                        </td>
                                    </tr>    
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            No data found...
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>


{{-- Add Modal --}}
<div class="modal fade modal-dark" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="successMessage" class="alert alert-success d-none"></div>
                <div id="errorAlert" class="alert alert-danger d-none"></div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input bg-dark" type="checkbox" id="uploadModeToggle" autocomplete="off">
                    <label class="form-check-label" for="uploadModeToggle">Bulk Upload Mode</label>
                </div>
                <form id="productAddForm">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control bg-dark text-white">
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Quantity</label>
                        <input type="number" id="quantity" name="quantity" class="form-control bg-dark text-white">
                        <div class="invalid-feedback" id="quantityError"></div>
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Images</label>
                        <input type="file" id="images" name="images[]" class="form-control bg-dark text-white" multiple>
                        <div class="invalid-feedback" id="imagesError"></div>
                    </div>
                    <button type="submit" class="btn btn-success">Save</button>
                </form>

                <form id="productBulkAddForm" class="d-none">
                    @csrf
                    <div class="mb-3">
                        <label for="sampleFile" class="form-label">Sample File</label>
                        <a href="{{ asset('files/products.xlsx') }}" class="btn btn-link" dwonload>dwonload</a>
                    </div>
                    <div class="mb-3">
                        <label for="bulkFile" class="form-label">Upload CSV/Excel File</label>
                        <input type="file" id="bulkFile" name="file" class="form-control bg-dark text-white">
                        <div class="invalid-feedback" id="bulkFileError"></div>
                    </div>
                    <button type="submit" class="btn btn-success">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade modal-dark" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="successMessageEdit" class="alert alert-success d-none"></div>
                <div id="errorAlertEdit" class="alert alert-danger d-none"></div>
                <form id="productEditForm">
                    @csrf
                    <input type="hidden" name="id" id="idEdit">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="nameEdit" name="name" class="form-control bg-dark text-white">
                        <div class="invalid-feedback" id="nameErrorEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Quantity</label>
                        <input type="number" id="quantityEdit" name="quantity" class="form-control bg-dark text-white">
                        <div class="invalid-feedback" id="quantityErrorEdit"></div>
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Images</label>
                        <input type="file" id="imagesEdit" name="images[]" class="form-control bg-dark text-white" multiple>
                        <div class="invalid-feedback" id="imagesErrorEdit"></div>
                    </div>
                    <button type="submit" class="btn btn-success">Save</button>
                </form>

                <div id="existingImages" class="me-3 row pt-3">
                    <!-- Existing images will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function () {

        $('#uploadModeToggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#productAddForm').addClass('d-none');
                $('#productBulkAddForm').removeClass('d-none');
            } else {
                $('#productAddForm').removeClass('d-none');
                $('#productBulkAddForm').addClass('d-none');
            }
        });

        $('#productAddForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $('.invalid-feedback').text('').hide();
            $('#errorAlert').text('').addClass('d-none');
            $('#successMessage').text('').addClass('d-none');

            $.ajax({
                url: "{{ route('product.add') }}",
                type: 'POST',
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
                        if (errors.file) $('#bulkFile').text(errors.file[0]).show();
                    }
                    
                    if (xhr.responseJSON.error) {
                        $('#errorAlert').text(xhr.responseJSON.error).removeClass('d-none');
                    }
                }
            });
        });

        $('#productBulkAddForm').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $('.invalid-feedback').text('').hide();
            $('#errorAlert').text('').addClass('d-none');
            $('#successMessage').text('').addClass('d-none');

            $.ajax({
                url: "{{ route('product.bulk.add') }}",
                type: 'POST',
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
                        if (errors.images) $('#imagesError').text(errors.images[0]).show();
                        if (errors.quantity) $('#quantityError').text(errors.quantity[0]).show();
                    }
                    
                    if (xhr.responseJSON.error) {
                        $('#errorAlert').text(xhr.responseJSON.error).removeClass('d-none');
                    }
                }
            });
        });

        $('.edit-button').on('click', function() {
            let productId = $(this).data('id');
            
            $.ajax({
                url: "{{ route('product.load', ':id') }}".replace(':id', productId),
                type: 'GET',
                success: function(product) {
                    $('#idEdit').val(product.id);
                    $('#nameEdit').val(product.name);
                    $('#quantityEdit').val(product.quantity);
                    
                    let imagesHtml = '';
                    product.images.forEach(function(image) {
                        imagesHtml += `
                            <div class="p-2 col-lg-4 col-sm-12 col-md-6">
                                <img src="{{ asset('storage') }}/${image.image}" class="img-fluid mb-2" style="max-width: 100px;">
                                <button type="button" class="btn btn-danger btn-sm delete-image mb-2" data-id="${image.id}">Delete</button>
                            </div>`;
                    });
                    $('#existingImages').html(imagesHtml);

                    $('#editProductModal').modal('show');
                },
                error: function(xhr) {
                    $('#error').text(xhr.responseJSON.error).removeClass('d-none');
                    setTimeout(function () {
                        $('#error').addClass('d-none');
                    }, 3000);
                    console.error(xhr);
                }
            });
        });

        $('#productEditForm').on('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            
            $.ajax({
                url: "{{ route('product.update') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#successMessageEdit').text(response['success']).removeClass('d-none');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON.errors;
                    if (errors) {
                        if (errors.name) $('#nameErrorEdit').text(errors.name[0]).show();
                        if (errors.images) $('#imagesErrorEdit').text(errors.images[0]).show();
                        if (errors.quantity) $('#quantityErrorEdit').text(errors.quantity[0]).show();
                    }
                   
                    if (xhr.responseJSON.error) {
                        $('#errorAlertEdit').text(xhr.responseJSON.error).removeClass('d-none');
                    }
                }
            });
        });

        $(document).on('click', '.delete-image', function() {
            let imageId = $(this).data('id');
            let $imageElement = $(this).parent();
            
            $.ajax({
                url: "{{ route('product.image.delete', ':id') }}".replace(':id', imageId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $imageElement.remove();
                    } else {
                        console.log(response);
                    }
                },
                error: function(xhr) {
                    $('#error').text(xhr.responseJSON.error).removeClass('d-none');

                    setTimeout(function () {
                        $('#error').addClass('d-none');
                    }, 3000);
                    console.log(xhr);
                }
            });
        });

        toggleBulkDeleteContainer();

        function toggleBulkDeleteContainer() {
            let selectedProducts = $('.productCheckbox:checked').length;
            $('#selectedCount').text(selectedProducts);

            if (selectedProducts > 0) {
                $('#bulkDeleteContainer').removeClass('d-none');
            } else {
                $('#bulkDeleteContainer').addClass('d-none');
            }

            let allChecked = $('.productCheckbox').length === $('.productCheckbox:checked').length;
            $('#checkAll').prop('checked', allChecked);
        }

        $('#checkAll').on('click', function () {
            $('.productCheckbox').prop('checked', this.checked);
            toggleBulkDeleteContainer();
        });

        $('.productCheckbox').on('change', function () {
            toggleBulkDeleteContainer();
        });

        $('#cancelBulkDelete').on('click', function () {
            $('.productCheckbox').prop('checked', false);
            toggleBulkDeleteContainer();
        });

        $('#confirmBulkDelete').on('click', function () {
            let selectedIds = $('.productCheckbox:checked').map(function () {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) return;

            $.ajax({
                url: "{{ route('product.bulk.delete') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    ids: selectedIds
                },
                success: function (response) {
                    location.reload();
                },
                error: function (xhr) {
                    $('#error').text(xhr.responseJSON.error).removeClass('d-none');

                    setTimeout(function () {
                        $('#error').addClass('d-none');
                    }, 3000);
                    console.log(xhr);
                }
            });
        });

        $(document).on('click', '.delete-product', function() {
            let productId = $(this).data('id');
            let $productElement = $(this).closest('tr');
            
            $.ajax({
                url: "{{ route('product.delete', ':id') }}".replace(':id', productId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $productElement.remove();
                    } else {
                        console.log(response);
                    }
                },
                error: function(xhr) {
                    $('#error').text(xhr.responseJSON.error).removeClass('d-none');

                    setTimeout(function () {
                        $('#error').addClass('d-none');
                    }, 3000);
                    console.log(xhr);
                }
            });
        });
    });
</script>
@endpush

</x-app-layout>