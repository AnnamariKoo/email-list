document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("email_list_form");
  if (!form) return;
  form.addEventListener("submit", function (event) {
    event.preventDefault();
    const formData = new FormData(form);
    const formObj = Object.fromEntries(formData);
    const errorDiv = document.getElementById("form_error");
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    for (const [key, value] of Object.entries(formObj)) {
      if (key === "_wpnonce" || key === "_wp_http_referer") continue;
      if (!value.trim() || value.trim().length < 2) {
        let fieldName = key.charAt(0).toUpperCase() + key.slice(1);
        if (key === "email") {
          errorDiv.textContent =
            "Sähköpostiosoitteessa on liian vähän kirjaimia.";
        } else {
          errorDiv.textContent = `${fieldName} on tyhjä tai siinä on liian vähän kirjaimia.`;
        }
        errorDiv.style.visibility = "visible";
        return;
      }

      if (key === "email" && !emailPattern.test(value)) {
        errorDiv.textContent = "Sähköpostiosoite ei ole oikeassa muodossa.";
        errorDiv.style.visibility = "visible";
        return;
      }
    }
    errorDiv.textContent = "";
    errorDiv.style.display = "none";

    const params = new URLSearchParams();
    for (const pair of formData) {
      params.append(pair[0], pair[1]);
    }

    fetch(window.emailListPluginRestUrl.restUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: params.toString(),
    })
      .then((response) => {
        if (!response.ok) {
          errorDiv.textContent = "Viestiä ei lähetetty. Yritä uudelleen.";
          errorDiv.style.visibility = "visible";
          return;
        }
        return response.json();
      })
      .then((data) => {
        if (!data) return;
        form.style.display = "none";
        const successDiv = document.getElementById("form_success");
        successDiv.textContent = data;
        successDiv.style.display = "block";
        successDiv.style.opacity = 0;
        setTimeout(() => {
          successDiv.style.transition = "opacity 0.05s";
          successDiv.style.opacity = 1;
        }, 10);
      })
      .catch((error) => {
        errorDiv.textContent = "Viestiä ei lähetetty. Yritä uudelleen.";
        errorDiv.style.visibility = "visible";
        console.error("Error:", error);
      });
  });
});
