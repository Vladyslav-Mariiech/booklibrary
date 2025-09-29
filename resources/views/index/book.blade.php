@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Book List</h1>

        <div class="filter-panel">
            <form method="GET" action="{{ route('books.index') }}">
                <div class="filter-search-group">
                    <input type="text"
                           name="search"
                           placeholder="Search by title, author, or description..."
                           value="{{ request('search') }}">
                    <button type="submit">Search</button>
                    @if(request('search'))
                        <a href="{{ route('books.index', ['sort' => request('sort')]) }}"
                           class="btn-reset">
                            Reset
                        </a>
                    @endif
                </div>

                <div class="sort-controls">
                    <span>Sort by title:</span>
                    <a href="{{ route('books.index', ['sort' => 'asc', 'search' => request('search')]) }}"
                       class="{{ request('sort', 'asc') === 'asc' ? 'active-sort' : '' }}">
                        ↑ A-Z
                    </a>
                    <a href="{{ route('books.index', ['sort' => 'desc', 'search' => request('search')]) }}"
                       class="{{ request('sort') === 'desc' ? 'active-sort' : '' }}">
                        ↓ Z-A
                    </a>
                </div>
            </form>
        </div>

        <div class="results-info">
            Showing {{ $books->count() }} of {{ $books->total() }} books
            @if(request('search'))
                for query "{{ request('search') }}"
            @endif
        </div>

        @if($books->count())
            <div class="book-list-grid">
                @foreach($books as $book)
                    <div class="book-card">
                        <div class="book-card-content">
                            <div class="book-card-image-wrapper">
                                @if($book->image)
                                    <img src="{{ asset('storage/' . $book->image) }}"
                                         alt="{{ $book->title }}"
                                         class="book-card-image">
                                @else
                                    <div class="book-card-no-image">
                                        No cover
                                    </div>
                                @endif
                            </div>

                            <div class="book-card-info">
                                <h3>{{ $book->title }}</h3>
                                <p>
                                    <strong>Authors:</strong>
                                    @if($book->authors->count())
                                        @foreach($book->authors as $author)
                                            {{ $author->surname }} {{ $author->name }}{{ $author->patronymic ? ' ' . $author->patronymic : '' }}@if(!$loop->last), @endif
                                        @endforeach
                                    @else
                                        Not specified
                                    @endif
                                </p>
                                @if($book->published_at)
                                    <p>
                                        <strong>Published Date:</strong> {{ $book->published_at->format('d.m.Y') }}
                                    </p>
                                @endif

                                @if($book->description)
                                    <p class="book-card-description">
                                        {{ Str::limit($book->description, 200) }}
                                    </p>
                                @endif
                                <div class="book-card-actions">
                                    <button type="button"
                                            class="btn btn-primary detailsBtn"
                                            onclick="showBookDetails({{ $book->id }})"
                                            data-book-id="{{ $book->id }}"
                                            data-book-title="{{ $book->title }}"
                                            data-book-description="{{ $book->description }}"
                                            data-book-published="{{ $book->published_at ? $book->published_at->format('d.m.Y') : '' }}"
                                            data-book-image="{{ $book->image ? asset('storage/' . $book->image) : '' }}"
                                            data-book-authors="@foreach($book->authors as $author){{ $author->surname }} {{ $author->name }}{{ $author->patronymic ? ' ' . $author->patronymic : '' }}@if(!$loop->last), @endif @endforeach">
                                        Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pagination-wrapper">
                {{ $books->links('pagination::bootstrap-5') }}
            </div>
        @else
            <p style="text-align: center; margin-top: 30px; font-style: italic; color: #6c757d;">No books found for your query.</p>
        @endif
    </div>

    <div class="modal" id="bookDetailsModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h5 class="modal-title" id="bookDetailsModalLabel">Book Details</h5>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="book-details-content">
                    <div class="book-details-row">
                        <div class="book-details-image-col">
                            <img id="modal-book-image" src="" alt="" class="book-details-image">
                        </div>
                        <div class="book-details-info-col">
                            <h3 id="modal-book-title"></h3>
                            <p><strong>Authors:</strong> <span id="modal-book-authors"></span></p>
                            <p id="modal-book-published-wrapper"><strong>Published Date:</strong> <span id="modal-book-published"></span></p>
                            <div id="modal-book-description-wrapper">
                                <h5>Description:</h5>
                                <p id="modal-book-description"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
            </div>
        </div>
    </div>
@endsection
