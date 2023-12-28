function flashMessage(el, bg_color, message) {
    const flashMessage = document.getElementById(el);
    flashMessage.classList.remove(...flashMessage.classList);
    flashMessage.classList.add("text-center");
    flashMessage.classList.add("alert");
    flashMessage.classList.add(bg_color);
    flashMessage.innerHTML = message;

    setTimeout(() => {
        flashMessage.style.display = "block";
        flashMessage.classList.add("show");
    }, 500);

    setTimeout(() => {
        flashMessage.classList.remove("show");
        setTimeout(() => {
            flashMessage.style.display = "none";
        }, 500);
    }, 5000);
}