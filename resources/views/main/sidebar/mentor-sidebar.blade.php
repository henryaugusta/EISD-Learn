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

{{-- CLASS --}}
<li class="nav-item {{ Request::is('lesson/manage_v2') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('lesson/manage_v2') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/Icon_Side_Bar/class.svg"
             class="nav-ok-logo"
             style="width: 14%;
                    height: auto;
                    margin-top: 5px;
                    margin-right: 10px;"
        >
        <p style="margin: 0; {{ (Request::is('lesson/manage_v2')) ? 'color: white !important;' : '' }}"
           class="{{ (Request::is('lesson/manage_v2')) ? 'text-white active' : '' }}">Class</p>
    </a>
</li>

{{-- EXAM --}}
<li class="nav-item {{ Request::is('exam/*') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('exam/manage-exam-v2') }}" style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/Icon_Side_Bar/Exam.svg"
             class="nav-ok-logo"
             style="width: 14%;
                height: auto;
                min-width: 14%!important;
                margin-top: 5px;
                "
        >
        <p class="{{ Request::is('exam/*') ? 'text-white' : ''}}"
           style="margin-left: 10px; {{ (Request::is('exam/*')) ? 'color: white !important;' : '' }}">Exam</p>
    </a>
</li>

{{-- DASHBOARD --}}
<li class="nav-item {{ Request::is('dashboard/*') ? 'active' : '' }}" style="display: flex; justify-content: center;">
    <a href="{{ url('/dashboard/mentor') }}"  style="display: flex; align-items: center;">
        <img src="{{URL::to('/')}}/home_assets/img/Icon_Side_Bar/Dashboard.svg"
             class="nav-ok-logo"
             style="width: 14%;
                height: auto;
                margin-top: 5px;
                "
        >
        <p class="{{ Request::is('dashboard/*') ? 'text-white' : ''}}"
           style="{{ Request::is('dashboard/*') ? 'color: white !important;' : '' }} margin-left: 10px;">Dashboard</p>
    </a>
</li>