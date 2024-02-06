{assign "navs" [ 
    ["dashboard", "Dashboard"], 
    ["billing","Balance & Billing"], 
    ["plan", "Manage Plan"], 
    ["devices", "Devices And App"],
    ["account", "Account Settings"] 
]}

<div class="col-2 h-100 d-flex flex-column justify-content-between">
    <div>
        <div class="py-4 k2d ps-2">
            <a class="text-light fs-4 link-underline link-underline-opacity-0" href="/user/dashboard">
                <img src="/assets/icons/logo.svg" alt="SVG Image">
                <span>IRONLINK</span>
            </a>
        </div>
        <nav class="nav fs-5 fw-light d-flex flex-column">
            {foreach $navs as $nav}
                <a class="nav-link me-2 px-0 text-gray" href="/user/{$nav[0]}">
                    {if strpos($smarty.server.REQUEST_URI, "/user/{$nav[0]}") !== false }
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
    <div class="d-flex flex-column gap-4 ps-4 mb-5 fs-5">
        {* <div class="card p-3 bg-quaternary text-light">
            <div>Your plan: basic</div>
            <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
            <button class="btn btn-info">change plan</button>
        </div> *}
        <div>
            <a href="/user/faq" class="fw-light text-light link-underline link-underline-opacity-0">
                <i class="bi bi-question-circle me-3"></i>
                <span>Get Help</span>
            </a>
        </div>
        <button class="btn btn-outline-info fw-normal">
            Sign Out
        </button>
    </div>
</div>