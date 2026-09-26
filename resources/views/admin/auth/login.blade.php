@extends('layouts.admin')

@section('title', 'Sign in')

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <a href="{{ route('home') }}" class="logo">{{ config('shop.name') }}<span class="dot">.</span></a>
            <div class="card card-pad">
                <h1 style="font-size:24px">Staff sign in</h1>
                <p class="muted small" style="margin:4px 0 20px">Manage books, categories and orders.</p>

                <form method="post" action="{{ route('admin.login') }}" class="form-grid">
                    @csrf
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="input @error('email') is-invalid @enderror" autocomplete="username" required autofocus>
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" class="input" autocomplete="current-password" required>
                    </div>
                    <label class="check"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                    <button type="submit" class="btn btn-block">Sign in</button>
                </form>
            </div>
        </div>
    </div>
@endsection
