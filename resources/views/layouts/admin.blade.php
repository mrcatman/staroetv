@extends('layouts.default')
@section('content')
    <div class="admin-panel">
        <div class="admin-panel__top">
            <div class="admin-panel__name">Панель управления сайтом</div>
            <div class="admin-panel__links">
                <a class="admin-panel__link" href="/admin/channels">
                    <i class="fa fa-tv"></i>Телеканалы
                </a>
                <a class="admin-panel__link" href="/admin/channels/order">
                    <i class="fa fa-sort-amount-up"></i>Порядок телеканалов
                </a>
                <a class="admin-panel__link" href="/admin/permissions">
                    <i class="fa fa-id-card"></i>Разрешения
                </a>
                <a class="admin-panel__link" href="/admin/smiles">
                    <i class="fa fa-smile"></i>Смайлы
                </a>
            </div>
        </div>
        <div class="admin-panel__content">
            @yield('admin_content')
        </div>

    </div>
</div>
@endsection