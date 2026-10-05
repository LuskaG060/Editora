document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const botao = document.getElementById("toggleSidebar");

    if (!sidebar || !botao) return;

    botao.addEventListener("click", () => {
        sidebar.classList.toggle("fechada");
        document.body.classList.toggle("sidebar-fechada");

        if (sidebar.classList.contains("fechada")) {
            botao.innerHTML = "&#10095;"; // ❯
        } else {
            botao.innerHTML = "&#10094;"; // ❮
        }
    });
});
