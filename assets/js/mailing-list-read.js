// Only run on the admin list table
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".mailing-list-read").forEach(function (checkbox) {
    checkbox.addEventListener("change", function () {
      const postId = this.getAttribute("data-id");
      const isRead = this.checked ? 1 : 0;

      fetch(ajaxurl, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=update_mailing_list_read&post_id=${postId}&read=${isRead}&_wpnonce=${window.mailingListReadNonce}`,
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) {
            alert("Virhe tallennettaessa luettu-tilaa!");
          }
        });
    });
  });
});
