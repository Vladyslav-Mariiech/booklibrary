@extends('layouts.crud')

@section('content')
    <div class="container">
        <h1>Authors</h1>

        <!-- Control Panel -->
        <div class="control-panel">
            <button class="btn btn-primary" data-modal-target="#authorModal">
                Add Author
            </button>

            <!-- Search and Sort -->
            <div class="search-panel">
                <form method="GET" action="{{ route('crud.authors.index') }}" class="search-form">
                    <div class="search-group">
                        <input type="text" name="search"
                               placeholder="Search by surname or name..."
                               value="{{ request('search') }}"
                               class="search-input">
                        <button type="submit" class="btn btn-secondary">Search</button>
                        @if(request('search'))
                            <a href="{{ route('crud.authors.index') }}" class="btn btn-secondary">Reset</a>
                        @endif
                    </div>
                </form>

                <div class="sort-group">
                    <label>Sort by surname:</label>
                    <a href="{{ route('crud.authors.index', ['sort' => 'asc', 'search' => request('search')]) }}"
                       class="btn btn-sm {{ request('sort', 'asc') === 'asc' ? 'btn-primary' : 'btn-secondary' }}">
                        ↑ A-Z
                    </a>
                    <a href="{{ route('crud.authors.index', ['sort' => 'desc', 'search' => request('search')]) }}"
                       class="btn btn-sm {{ request('sort') === 'desc' ? 'btn-primary' : 'btn-secondary' }}">
                        ↓ Z-A
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        <!-- Authors Table -->
        <table class="table">
            <thead>
            <tr>
                <th>Surname</th>
                <th>Name</th>
                <th>Patronymic</th>
                <th>Full Name</th>
                <th>Books Count</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($authors as $author)
                <tr>
                    <td>{{ $author->surname }}</td>
                    <td>{{ $author->name }}</td>
                    <td>{{ $author->patronymic ?: '-' }}</td>
                    <td><strong>{{ $author->full_name }}</strong></td>
                    <td>{{ $author->books_count ?? $author->books()->count() }}</td>
                    <td>
                        <button class="btn btn-warning btn-sm editAuthorBtn"
                                data-id="{{ $author->id }}">
                            Edit
                        </button>

                        <form action="{{ route('crud.authors.destroy', $author) }}"
                              method="POST" style="display:inline;" class="delete-author-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No authors found</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($authors->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $authors->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    <!-- Modal "Add Author" -->
    <div class="modal" id="authorModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title">Add Author</h5>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="createAuthorForm" action="{{ route('crud.authors.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Surname *</label>
                        <input type="text" name="surname" class="form-control"
                               required minlength="3" maxlength="255"
                               placeholder="Enter surname (min. 3 characters)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control"
                               required maxlength="255"
                               placeholder="Enter name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Patronymic</label>
                        <input type="text" name="patronymic" class="form-control"
                               maxlength="255"
                               placeholder="Enter patronymic (optional)">
                    </div>

                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal "Edit Author" -->
    <div class="modal" id="editAuthorModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title">Edit Author</h5>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editAuthorForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-author-id" name="author_id">

                    <div class="mb-3">
                        <label class="form-label">Surname *</label>
                        <input type="text" name="surname" id="edit-surname" class="form-control"
                               required minlength="3" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="edit-name" class="form-control"
                               required maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Patronymic</label>
                        <input type="text" name="patronymic" id="edit-patronymic" class="form-control"
                               maxlength="255">
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                </form>
            </div>
        </div>
    </div>
@endsection
