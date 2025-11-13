// Validação de senha forte
function validarSenha(senha) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(senha);
    const hasLowerCase = /[a-z]/.test(senha);
    const hasNumbers = /\d/.test(senha);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(senha);

    if (senha.length < minLength) {
        return { valida: false, mensagem: "A senha deve ter pelo menos 8 caracteres." };
    }
    if (!hasUpperCase) {
        return { valida: false, mensagem: "A senha deve conter pelo menos uma letra maiúscula." };
    }
    if (!hasLowerCase) {
        return { valida: false, mensagem: "A senha deve conter pelo menos uma letra minúscula." };
    }
    if (!hasNumbers) {
        return { valida: false, mensagem: "A senha deve conter pelo menos um número." };
    }
    if (!hasSpecialChar) {
        return { valida: false, mensagem: "A senha deve conter pelo menos um caractere especial (!@#$%^&*)." };
    }

    return { valida: true, mensagem: "Senha forte!" };
}

// Validação de força da senha em tempo real
document.addEventListener('DOMContentLoaded', function() {
    const senhaInput = document.getElementById('senha_user');
    const confirmSenhaInput = document.getElementById('Csenha_user');
    const senhaFeedback = document.getElementById('senha-feedback');

    if (senhaInput) {
        senhaInput.addEventListener('input', function() {
            const resultado = validarSenha(this.value);

            if (senhaFeedback) {
                senhaFeedback.textContent = resultado.mensagem;
                senhaFeedback.className = resultado.valida ? 'text-success small' : 'text-danger small';
            }
        });
    }

    if (confirmSenhaInput && senhaInput) {
        confirmSenhaInput.addEventListener('input', function() {
            const senhasIguais = this.value === senhaInput.value;
            const confirmFeedback = document.getElementById('confirm-feedback');

            if (confirmFeedback) {
                confirmFeedback.textContent = senhasIguais ? 'As senhas coincidem!' : 'As senhas não coincidem!';
                confirmFeedback.className = senhasIguais ? 'text-success small' : 'text-danger small';
            }
        });
    }
});

// Validação do formulário de cadastro
function validarFormularioCadastro() {
    const senha = document.getElementById('senha_user').value;
    const confirmSenha = document.getElementById('Csenha_user').value;
    const resultado = validarSenha(senha);

    if (!resultado.valida) {
        alert(resultado.mensagem);
        return false;
    }

    if (senha !== confirmSenha) {
        alert('As senhas não coincidem!');
        return false;
    }

    return true;
}