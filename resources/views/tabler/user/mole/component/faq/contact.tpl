<div class="col-5 p-3">
    <div class="d-flex flex-column align-items-center gap-4">
        <div class="fs-4 fw-bold text-center">Have any other question?</div>
        <div class="fs-6 fw-light text-center">Couldn’t find you r answer in our FAQ? contact us and we’ll be with you
            shortly</div>
        <a href="https://t.me/irontest_bot" class="btn btn-outline-info fw-7 fw-light d-flex align-items-center ps-0 py-1">
            <img src="/assets/icons/telegram.svg" />
            <span>Contact us on Telegram</span>
        </a>
    </div>
    <hr class="m-5" />

    <div class="fs-4 fw-bold text-center">Contact With Us</div>
    <form class="d-flex flex-column" hx-post="/user/ticket" hx-swap="none">
        <input type="hidden" name="title" value="user faq">
        <div class="mb-3">
            <label class="form-label fs-7 fw-light">Category</label>
            <select class="form-select text-gray fs-6 fw-light bg-quinary border-0 mt-1 py-3" name="type" required>
                <option value="" disabled selected>select category</option>
                <option value="billing">billing</option>
                <option value="advice">advice</option>
                <option value="usage">usage</option>
                <option value="others">others</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label fs-7 fw-light">Enter Your Name (Optional)</label>
            <input type="text" name="comment" class="form-control bg-quinary border-0 text-light fs-6 fw-light py-3"
                placeholder="name@example.com">
        </div>
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label fs-7 fw-light">Enter Your Email <span
                    class="text-danger">*</span> </label>
            <input type="email" name="email" class="form-control bg-quinary border-0 text-light fs-6 fw-light py-3"
                placeholder="name@example.com" required>
        </div>
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label fs-7 fw-light">Enter Your Message</label>
            <textarea name="message" class="form-control bg-quinary border-0 text-light fs-6 fw-light py-3" rows="3"
                placeholder="Lorem ipsum dolor sit amet,  adipiscing elit. Etiam nec blandit dolor?"
                required></textarea>
        </div>
        <div class="d-flex justify-content-center">
            <button class="btn btn-info fs-5 fw-normal" type="submit">Send messange</button>
        </div>
    </form>
</div>