@extends('layouts.app')

@section('title', 'Edit Company')

@section('content')

<div class="card">

	<h1>Edit Company</h1>

	<form action="{{ route('companies.update', $company) }}" method="POST">

		@csrf
		@method('PUT')

		<div class="form-group">
			<label for="name">Company Name</label>

			<input
				type="text"
				id="name"
				name="name"
				value="{{ old('name', $company->name) }}"
				maxlength="150"
				required
			>

			@error('name')
				<div class="error">{{ $message }}</div>
			@enderror
		</div>

		<div class="form-group">
			<label for="code">Company Code</label>

			<input
				type="text"
				id="code"
				name="code"
				value="{{ old('code', $company->code) }}"
				maxlength="50"
				required
			>

			@error('code')
				<div class="error">{{ $message }}</div>
			@enderror
		</div>

		<div class="form-group">
			<label for="address">Address</label>

			<textarea
				id="address"
				name="address"
				rows="3"
				maxlength="255"
			>{{ old('address', $company->address) }}</textarea>

			@error('address')
				<div class="error">{{ $message }}</div>
			@enderror
		</div>

		<div class="form-group">
			<label for="phone">Phone</label>

			<input
				type="text"
				id="phone"
				name="phone"
				value="{{ old('phone', $company->phone) }}"
				maxlength="30"
			>

			@error('phone')
				<div class="error">{{ $message }}</div>
			@enderror
		</div>

		<div class="form-group">
			<label for="email">Email</label>

			<input
				type="email"
				id="email"
				name="email"
				value="{{ old('email', $company->email) }}"
				maxlength="150"
			>

			@error('email')
				<div class="error">{{ $message }}</div>
			@enderror
		</div>

		<button type="submit" class="btn btn-primary">
			Update Company
		</button>

		<a href="{{ route('companies.index') }}" class="btn btn-secondary">
			Cancel
		</a>

	</form>

</div>

@endsection
