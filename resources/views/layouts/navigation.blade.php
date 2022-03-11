<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Dashboard</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" id="start_scan" data-href="{{route('data.scan')}}" href="#">Scan Data</a>
            </li>
            <li class="nav-item">
                <a  id="clear_cache" href="{{route('cache.clear')}}" class="nav-link" href="#">Cache clear</a>
            </li>

            <li class="dropdown">
                <a href="#" data-toggle="dropdown" role="button" aria-expanded="true" class="dropdown-toggle">
                    {{ Auth::user()->name }} <span class="caret"></span></a>
                <div class="dropdown-menu">

                    <form class="nav-link dropdown-item" method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                                         onclick="event.preventDefault();
                                                this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
