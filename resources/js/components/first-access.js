document.addEventListener('DOMContentLoaded', function () {
    // Verifica se existe um modal de primeiro acesso
    const firstAccessModal = document.getElementById('firstAccessModal');

    if (firstAccessModal) {
        // Inicializa o modal do Bootstrap
        const modal = new bootstrap.Modal(firstAccessModal);

        // Exibe o modal
        modal.show();

        // Impede que o modal seja fechado ao clicar fora ou com tecla ESC
        firstAccessModal.addEventListener('hide.bs.modal', function (event) {
            // Impede o fechamento do modal se não for pelo botão de submit
            if (!event.relatedTarget) {
                event.preventDefault();
            }
        });
    }
});
