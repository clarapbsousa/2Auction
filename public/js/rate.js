
// Seleciona os elementos
const openPopup = document.getElementById('report-button');
const closePopup = document.getElementById('closePopup');
const reportForm = document.getElementById('reportform');
const openSystemPopup = document.getElementById('submit-button');
const errorPopup = document.getElementById('error-popup');
const closeErrorPopup = document.getElementById('close-error-popup');
const successPopup = document.getElementById('success-popup');
const closeSuccessPopup = document.getElementById('close-success-popup');
const loadingPopup = document.getElementById('loading-popup');
const checkboxErrorPopup = document.getElementById('checkbox-error-popup');
const closeCheckboxErrorPopup = document.getElementById('close-checkbox-error-popup');
const rateFormContainer = document.getElementById('rateFormContainer');
const rateButton = document.getElementById('rateButton');
const ratePopup = document.getElementById('ratePopup');
const closeRatePopup = document.getElementById('closeRatePopup');


//Rates
// Função para mostrar o popup de Rate User
rateButton.addEventListener('click', () => {
    showPopup(ratePopup); // Reutiliza a função showPopup
});

// Função para fechar o popup de Rate User
closeRatePopup.addEventListener('click', () => {
    hidePopup(ratePopup); // Reutiliza a função hidePopup
});

// Fecha o popup ao clicar fora do conteúdo
ratePopup.addEventListener('click', (e) => {
    if (e.target === ratePopup) {
        hidePopup(ratePopup);
    }
});

//Rate button
rateButton.addEventListener('click', () => {
    // Alterna a classe 'active' para mostrar/esconder o formulário
    rateFormContainer.classList.toggle('active');
});


///////////////////////////////////
//Reports
//////////////////////////////////

// Abre o popup
openPopup.addEventListener('click', () => {
    reportForm.style.display = 'flex';
});

// Fecha o popup
closePopup.addEventListener('click', () => {
    reportForm.style.display = 'none';
});


openSystemPopup.addEventListener('click', () => {
    loadingPopup.style.display = 'flex';
});


function showPopup(popup) {
    popup.style.display = 'flex';
}

function hidePopup(popup) {
    popup.style.display = 'none';
}




// Gerencia a submissão do formulário
document.getElementById('reportform').addEventListener('submit', async (event) => {
    event.preventDefault(); // Evita o envio padrão do formulário




    // Verifica se pelo menos uma checkbox está selecionada
    const selectedReasons = Array.from(document.querySelectorAll('input[name="reasons[]"]:checked'))
        .map((checkbox) => checkbox.value);

    if (selectedReasons.length === 0) {
        checkboxErrorPopup.style.display = 'flex';
        return;
    }

    // Exibe o popup de loading
    loadingPopup.style.display = 'flex';

    // Prepara os dados do formulário
    const formData = new FormData(event.target);

    try {
        // Realiza a submissão via fetch
        const response = await fetch(event.target.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('Failed to submit the report.');
        }

        // Oculta o popup de loading e exibe o de sucesso
        loadingPopup.style.display = 'none';
        successPopup.style.display = 'flex';
    } catch (error) {
        // Oculta o popup de loading e exibe o de erro
        loadingPopup.style.display = 'none';
        errorPopup.style.display = 'flex';
    }

    setTimeout(() => {
        checkboxErrorPopup.style.display = 'none';
    }, 1500); 
    
    setTimeout(() => {
        successPopup.style.display = 'none';
    }, 1500);
    
    setTimeout(() => {
        errorPopup.style.display = 'none';
    }, 1500); 

});

// Eventos para fechar os popups de erro e sucesso
closeErrorPopup.addEventListener('click', () => hidePopup(errorPopup));
closeSuccessPopup.addEventListener('click', () => hidePopup(successPopup));
closeCheckboxErrorPopup.addEventListener('click', () => hidePopup(checkboxErrorPopup));




