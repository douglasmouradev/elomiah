(() => {
  const cep = document.querySelector('#cep');
  if (!cep) return;

  const fields = {
    logradouro: document.querySelector('#logradouro'),
    bairro: document.querySelector('#bairro'),
    cidade: document.querySelector('#cidade'),
    estado: document.querySelector('#estado'),
  };
  const hint = document.querySelector('#cep-hint');
  const numero = document.querySelector('#numero');

  const setBusy = (busy) => {
    Object.values(fields).forEach((el) => {
      if (!el) return;
      el.readOnly = busy;
    });
  };

  const lookup = async () => {
    const digits = cep.value.replace(/\D+/g, '');
    cep.value = digits.replace(/(\d{5})(\d)/, '$1-$2').slice(0, 9);
    if (digits.length !== 8) return;
    if (hint) hint.textContent = 'Buscando endereço…';
    try {
      const res = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
      const data = await res.json();
      if (data.erro) {
        if (hint) hint.textContent = 'CEP não encontrado. Preencha o endereço manualmente.';
        setBusy(false);
        return;
      }
      if (fields.logradouro) fields.logradouro.value = data.logradouro || '';
      if (fields.bairro) fields.bairro.value = data.bairro || '';
      if (fields.cidade) fields.cidade.value = data.localidade || '';
      if (fields.estado) fields.estado.value = data.uf || '';
      if (hint) hint.textContent = 'Endereço preenchido. Informe o número.';
      numero?.focus();
    } catch {
      if (hint) hint.textContent = 'Não foi possível consultar o CEP. Preencha manualmente.';
      setBusy(false);
    }
  };

  let lastDigits = '';
  cep.addEventListener('blur', lookup);
  cep.addEventListener('input', () => {
    const digits = cep.value.replace(/\D+/g, '');
    if (digits.length === 8 && digits !== lastDigits) {
      lastDigits = digits;
      lookup();
    }
  });
})();
