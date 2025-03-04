@extends('layouts.app')

@section('content')
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">{{ $title }}</h2>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Nome</th>
                    <th class="p-3 text-left">Email</th>
                    <th class="p-3 text-left">Senha Padrão</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr class="border-b">
                        <td class="p-3">{{ $user->name }}</td>
                        <td class="p-3">{{ $user->email }}</td>
                        <td class="p-3">senha123</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
