document.addEventListener("DOMContentLoaded", function () {
  // Initial highlight for already-read rows
  document.querySelectorAll("tr.type-mailing_list").forEach(function (row) {
    const readCell = row.querySelector("input.mailing-list-read");
    if (readCell && readCell.checked) {
      row.classList.add("mailing-list-row-read");
    }
    // Listen for checkbox changes
    if (readCell) {
      readCell.addEventListener("change", function () {
        if (this.checked) {
          row.classList.add("mailing-list-row-read");
        } else {
          if (!confirm("Oletko varma, että haluat ottaa merkinnän pois?")) {
            this.checked = true;
            row.classList.add("mailing-list-row-read");
            return;
          }
          row.classList.remove("mailing-list-row-read");
        }
      });
    }
  });
});
