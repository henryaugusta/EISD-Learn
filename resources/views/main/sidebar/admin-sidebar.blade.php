{{-- IMPROVED MENU --}}
{{-- HOME --}}
<li class="nav-item {{ Request::is('home*') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('/home') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/Icon_Side_Bar/ic_home.svg"
             class="nav-ok-logo"
             style="width: 14%;
                    height: auto;
                    margin-top: 5px;
                    "
        >
        <p class="{{ Request::is('home/*') ? 'text-white active' : ''}}"
           style="margin-left: 10px; {{ (Request::is('home*')) ? 'color: white !important;' : '' }}">Home</p>
    </a>
</li>

{{-- Manage Mentor--}}
<li class="nav-item {{ Request::is('admin/manage-mentor') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('admin/manage-mentor') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/mentors.svg"
             class="nav-ok-logo"
             style="width: 14%; height: auto; margin-top: 5px; margin-right: 10px; color: white;"
        >
        <p style="margin: 0; {{ (Request::is('admin/manage-mentor')) ? 'color: white !important;' : '' }}"
           class="{{ (Request::is('admin/manage-mentor')) ? 'text-white active' : '' }}">Manage Mentor</p>
    </a>
</li>

{{-- CLASS CATEGORY--}}
<li class="nav-item {{ Request::is('lesson/category') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('lesson/category') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/ic-category.svg"
             class="nav-ok-logo"
             style="width: 14%;
                    height: auto;
                    margin-top: 5px;
                    margin-right: 10px;"
        >
        <p style="margin: 0; {{ (Request::is('lesson/category')) ? 'color: white !important;' : '' }}"
           class="{{ (Request::is('lesson/category')) ? 'text-white active' : '' }}">Class Category</p>
    </a>
</li>


{{-- CLASS CATEGORY--}}
<li class="nav-item {{ Request::is('registration-code-management') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('registration-code-management') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/partnership.svg"
             class="nav-ok-logo"
             style="width: 14%;
                    height: auto;
                    margin-top: 5px;
                    margin-right: 10px;"
        >
        <p style="margin: 0; {{ (Request::is('registration-code-management')) ? 'color: white !important;' : '' }}"
           class="{{ (Request::is('registration-code-management')) ? 'text-white active' : '' }}">Manage Partnership</p>
    </a>
</li>

