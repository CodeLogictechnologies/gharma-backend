<style>
    .main-sidebar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;

        .header-logo {
            /* height: 40px !important; */
            width: 65px;
            /* .ht-40{
                height: 50px !important;
            } */
        }

        .logo_name {
            p {
                font-size: 14px;
                font-weight: 500;
                margin: 0 !important;
            }
        }

        .main-menu li.active a {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

    }
</style>
<!-- Start::app-sidebar -->
<aside class="app-sidebar sticky" id="sidebar">

    <!-- Start::main-sidebar-header -->
    <div class="main-sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="header-logo">
            <?php
            $imgPath = !empty($siteSetting->img_logo) && \Illuminate\Support\Facades\Storage::exists('public/setting/' . $siteSetting->img_logo) ? asset('storage/setting/' . $siteSetting->img_logo) : asset('no-image.jpg');
            ?>
            <img src="{{ $imgPath }}" class="rounded-circle" alt="School Logo" width="37" height="37">
        </a>
        <div class="logo_name">
            <p>{{ $siteSetting->name }}</p>
        </div>
    </div>
    <!-- End::main-sidebar-header -->

    <!-- Start::main-sidebar -->
    <div class="main-sidebar" id="sidebar-scroll">

        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24"
                    viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">

                <li class="slide__category"><span class="category-name">One Time
                        Setting</span></li>
                {{-- 
                <li class="slide {{ request()->is('sitesetting') ? 'active' : '' }}">
                    <a href="{{ route('admin.sitesetting') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                            <path
                                d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z" />
                        </svg>
                        <span class="side-menu__label">Site Setting</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.aboutus') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M11 9h2V7h-2m1 13c-4.41 0-8-3.59-8-8s3.59-8 8-8s8 3.59 8 8s-3.59 8-8 8m0-18A10 10 0 0 0 2 12a10 10 0 0 0 10 10a10 10 0 0 0 10-10A10 10 0 0 0 12 2m-1 15h2v-6h-2z" />
                        </svg>
                        <span class="side-menu__label">About Us</span>
                    </a>
                </li> --}}
                @can('post.view')
                    <li class="slide">
                        <a href="{{ route('organization') }}" class="side-menu__item">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                                viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M18 15h-2v2h2m0-4h-2v2h2m2 4h-10v-2h2v-2h-2v-2h2v-2h-2V9h10M10 7H8V5h2m0 6H8V9h2m0 6H8v-2h2m0 6H8v-2h2M6 7H4V5h2m0 6H4V9h2m0 6H4v-2h2m0 6H4v-2h2m6-14v-2H2v18h20V7z" />
                            </svg>
                            <span class="side-menu__label">Organization</span>
                        </a>
                    </li>
                @endcan

                <li class="slide">
                    <a href="{{ route('role') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M12 1a5 5 0 1 0 0 10A5 5 0 0 0 12 1m0 2a3 3 0 1 1 0 6a3 3 0 0 1 0-6m0 10c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4m-6 4c.2-.71 2.7-2 6-2s5.8 1.29 6 2z" />
                        </svg>
                        <span class="side-menu__label">Role</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('permission') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5m0 2l6 2.18V11c0 3.9-2.6 7.66-6 8.93c-3.4-1.27-6-5.03-6-8.93V5.18M10 14l-2-2l-1.41 1.41L10 16.83l6.41-6.42L15 9z" />
                        </svg>
                        <span class="side-menu__label">Permission</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('user') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M18 15h-2v2h2m0-4h-2v2h2m2 4h-10v-2h2v-2h-2v-2h2v-2h-2V9h10M10 7H8V5h2m0 6H8V9h2m0 6H8v-2h2m0 6H8v-2h2M6 7H4V5h2m0 6H4V9h2m0 6H4v-2h2m0 6H4v-2h2m6-14v-2H2v18h20V7z" />
                        </svg>
                        <span class="side-menu__label">Users</span>
                    </a>
                </li>

                <li class="slide__category"><span class="category-name">Main Menu</span></li>


                {{-- our team start here --}}
                <li class="slide has-sub {{ request()->is('admin/memberCategory*') || request()->is('admin/member*') || request()->is('admin/timeinterval*') ? 'active open' : '' }}"
                    id="mainMenu">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16"
                            fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path
                                d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                        </svg>
                        <span class="side-menu__label">Category</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1">
                            <a href="javascript:void(0);">Sub Category</a>
                        </li>
                        <li class="slide" id="childMenu">
                            <a href="{{ route('category') }}" class="side-menu__item">Category</a>
                        </li>
                        <li class="slide" id="childMenu">
                            <a href="{{ route('subcategory') }}" class="side-menu__item">Sub Category</a>
                        </li>
                        <li class="slide" id="childMenu">
                            <a href="{{ route('item') }}" class="side-menu__item">Item</a>
                        </li>
                    </ul>
                </li>
                {{-- our team end here --}}
                {{-- 
                <li class="slide">
                    <a href="{{ route('admin.document') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                            <path
                                d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5" />
                        </svg>
                        <span class="side-menu__label">Document</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.message') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M4 4h16v12H5.17L4 17.17zm0-2c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm2 10h12v2H6zm0-3h12v2H6zm0-3h12v2H6z" />
                        </svg>
                        <span class="side-menu__label">Message From</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.post') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h8q.425 0 .713.288T14 4t-.288.713T13 5H5v14h14v-8q0-.425.288-.712T20 10t.713.288T21 11v8q0 .825-.587 1.413T19 21zm4-4q-.425 0-.712-.288T8 16t.288-.712T9 15h6q.425 0 .713.288T16 16t-.288.713T15 17zm0-3q-.425 0-.712-.288T8 13t.288-.712T9 12h6q.425 0 .713.288T16 13t-.288.713T15 14zm0-3q-.425 0-.712-.288T8 10t.288-.712T9 9h6q.425 0 .713.288T16 10t-.288.713T15 11zm9-2q-.425 0-.712-.288T17 8V7h-1q-.425 0-.712-.288T15 6t.288-.712T16 5h1V4q0-.425.288-.712T18 3t.713.288T19 4v1h1q.425 0 .713.288T21 6t-.288.713T20 7h-1v1q0 .425-.288.713T18 9" />
                        </svg>
                        <span class="side-menu__label">Post</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('admin.timeline') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M21 8c-1.5 0-2.3 1.4-1.9 2.5l-3.6 3.6c-.3-.1-.7-.1-1 0l-2.6-2.6c.4-1.1-.4-2.5-1.9-2.5c-1.4 0-2.3 1.4-1.9 2.5L3.5 16c-1.1-.3-2.5.5-2.5 2c0 1.1.9 2 2 2c1.4 0 2.3-1.4 1.9-2.5l4.5-4.6c.3.1.7.1 1 0l2.6 2.6c-.3 1 .5 2.5 2 2.5s2.3-1.4 1.9-2.5l3.6-3.6c1.1.3 2.5-.5 2.5-1.9c0-1.1-.9-2-2-2m-6 1l.9-2.1L18 6l-2.1-.9L15 3l-.9 2.1L12 6l2.1.9zM3.5 11L4 9l2-.5L4 8l-.5-2L3 8l-2 .5L3 9z" />
                        </svg>
                        <span class="side-menu__label">Timeline</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.gallery') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-card-image" viewBox="0 0 16 16">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                            <path
                                d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2zm13 1a.5.5 0 0 1 .5.5v6l-3.775-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12v.54L1 12.5v-9a.5.5 0 0 1 .5-.5z" />
                        </svg>
                        <span class="side-menu__label">Gallery</span>
                    </a>
                </li>


                <li class="slide">
                    <a href="{{ route('admin.donor') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg"class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path
                                d="M64 64C28.7 64 0 92.7 0 128L0 384c0 35.3 28.7 64 64 64l448 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64L64 64zm64 320l-64 0 0-64c35.3 0 64 28.7 64 64zM64 192l0-64 64 0c0 35.3-28.7 64-64 64zM448 384c0-35.3 28.7-64 64-64l0 64-64 0zm64-192c-35.3 0-64-28.7-64-64l64 0 0 64zM288 160a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" />
                        </svg>
                        <span class="side-menu__label">Our Donor</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('admin.program') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg"class="side-menu__icon" width="16" height="16"
                            viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                            <path
                                d="M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z" />
                        </svg>
                        <span class="side-menu__label">Program</span>
                    </a>
                </li>
                <li class="slide">
                    <a href="{{ route('admin.event') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"width="16" height="16"
                            fill="currentColor" class="bi bi-calendar-event" viewBox="0 0 16 16">
                            <path
                                d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z" />
                            <path
                                d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z" />
                        </svg>
                        <span class="side-menu__label">Event</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.history') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                            <path
                                d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z" />
                            <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z" />
                            <path
                                d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5" />
                        </svg>
                        <span class="side-menu__label">History</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.faq') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-patch-question" viewBox="0 0 16 16">
                            <path
                                d="M8.05 9.6c.336 0 .504-.24.554-.627.04-.534.198-.815.847-1.26.673-.475 1.049-1.09 1.049-1.986 0-1.325-.92-2.227-2.262-2.227-1.02 0-1.792.492-2.1 1.29A1.7 1.7 0 0 0 6 5.48c0 .393.203.64.545.64.272 0 .455-.147.564-.51.158-.592.525-.915 1.074-.915.61 0 1.03.446 1.03 1.084 0 .563-.208.885-.822 1.325-.619.433-.926.914-.926 1.64v.111c0 .428.208.745.585.745" />
                            <path
                                d="m10.273 2.513-.921-.944.715-.698.622.637.89-.011a2.89 2.89 0 0 1 2.924 2.924l-.01.89.636.622a2.89 2.89 0 0 1 0 4.134l-.637.622.011.89a2.89 2.89 0 0 1-2.924 2.924l-.89-.01-.622.636a2.89 2.89 0 0 1-4.134 0l-.622-.637-.89.011a2.89 2.89 0 0 1-2.924-2.924l.01-.89-.636-.622a2.89 2.89 0 0 1 0-4.134l.637-.622-.011-.89a2.89 2.89 0 0 1 2.924-2.924l.89.01.622-.636a2.89 2.89 0 0 1 4.134 0l-.715.698a1.89 1.89 0 0 0-2.704 0l-.92.944-1.32-.016a1.89 1.89 0 0 0-1.911 1.912l.016 1.318-.944.921a1.89 1.89 0 0 0 0 2.704l.944.92-.016 1.32a1.89 1.89 0 0 0 1.912 1.911l1.318-.016.921.944a1.89 1.89 0 0 0 2.704 0l.92-.944 1.32.016a1.89 1.89 0 0 0 1.911-1.912l-.016-1.318.944-.921a1.89 1.89 0 0 0 0-2.704l-.944-.92.016-1.32a1.89 1.89 0 0 0-1.912-1.911z" />
                            <path d="M7.001 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0" />
                        </svg>
                        <span class="side-menu__label">FAQ</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="{{ route('admin.ritual') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-ban" viewBox="0 0 16 16">
                            <path
                                d="M15 8a6.97 6.97 0 0 0-1.71-4.584l-9.874 9.875A7 7 0 0 0 15 8M2.71 12.584l9.874-9.875a7 7 0 0 0-9.874 9.874ZM16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0" />
                        </svg>
                        <span class="side-menu__label">Ritual</span>
                    </a>
                </li>

                <li class="slide {{ request()->is('admin/cerficicate*') ? 'active' : '' }}">
                    <a href="{{ route('admin.certificate') }}" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                            <path
                                d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702z" />
                            <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1z" />
                        </svg>
                        <span class="side-menu__label">Certificate/Award</span>
                    </a>
                </li>



                <li class="slide__category"><span class="category-name">Contact</span></li>
                <li class="slide has-sub {{ request()->is('admin/contact*') ? 'active open' : '' }}" id="mainMenu">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16"
                            height="16" fill="currentColor" class="bi bi-people-fill" viewBox="0 0 16 16">
                            <path
                                d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6m-5.784 6A2.24 2.24 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.3 6.3 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1zM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5" />
                        </svg>
                        <span class="side-menu__label">Contact</span>
                        <i class="fe fe-chevron-right side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide">
                            <a href="{{ route('admin.enquiry') }}" class="side-menu__item">Enquiry Details</a>
                        </li>
                    </ul>
                </li> --}}


                <!-- End::slide -->
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg></div>
        </nav>
        <!-- End::nav -->

    </div>
    <!-- End::main-sidebar -->

</aside>
<!-- End::app-sidebar -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#childMenu').off('click');
        $('#childMenu').on('click', function(event) {
            $('#mainMenu').addClass('open');
            event.stopPropagation();
            console.log("Clicked childMenu");
        });
    });
</script>
