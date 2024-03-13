{assign "navs" [ 
    ["dashboard", "Dashboard"], 
    ["billing","Balance & Billing"], 
    ["plan", "Manage Plan"], 
    ["devices", "Devices And App"],
    ["account", "Account Settings"] 
]}

<div class="col-2 h-100 d-flex flex-column justify-content-between">
    <div>
        <div class="py-4 k2d ps-3">
            <a class="text-light fs-4 link-underline link-underline-opacity-0 d-flex align-items-center"
                href="/user/dashboard">
                <img src="/assets/icons/logo.svg" alt="SVG Image" style="width: 33px;">
                <span class="ms-2">IRONLINK</span>
            </a>
        </div>
        <nav class="nav fs-5 fw-light d-flex flex-column">
            {foreach $navs as $nav}
                <a class="nav-link me-2 px-0 text-gray" href="/user/{$nav[0]}">
                    {if strpos($smarty.server.REQUEST_URI, "/user/{$nav[0]}") !== false || ( $nav[0] === "dashboard" &&
                        $smarty.server.REQUEST_URI === "/user") }
                        <img class="position-absolute" src="/assets/icons/line.svg" alt="icon">
                    {/if}
                    <div class="d-flex align-items-center ps-4">
                        <img src="/assets/icons/{$nav[0]}.svg" alt="SVG Image">
                        <span class="ms-3">{$nav[1]}</span>
                    </div>
                </a>
            {/foreach}
        </nav>
    </div>

    <div>
        <a class="nav-link me-2 px-0 text-gray d-flex align-items-center my-4" href="/user/faq">
            {if strpos($smarty.server.REQUEST_URI, "/user/faq") !== false }
                <img class="position-absolute" src="/assets/icons/line.svg" alt="icon">
            {/if}
            <div class="d-flex align-items-center ps-4">
                <i class="fs-3 bi bi-question-circle me-1 ms-3"></i>
                <span class="ms-3">Get Help</span>
            </div>
        </a>
        <div class="d-flex flex-column gap-4 ps-4 mb-5 fs-5">
            <a class="btn btn-outline-info fw-normal" href="/user/logout">
                Sign Out
            </a>
        </div>
    </div>
</div>