const modalshow = document.querySelector("#modal-show");
const alert = document.createElement("div");

export default function Alert(text = "") {
  alert.setAttribute("id", "alertbox");
  alert.innerHTML = `
    <div class='position-fixed w-100 h-100 top-0 start-0' style='background: rgba(0, 0, 0, .5); z-index:100000'>
      <div class='card position-absolute top-50 start-50 translate-middle border' style='width:300px'>
        <div class='card-header d-flex justify-content-between align-items-center px-2 py-1 border-0'>
          <span class='d-flex align-items-center'>
            <i class="bi bi-exclamation-circle"></i> <span class='ms-2'>${location.protocol}://${location.hostname}</span>
          </span>
          <span class='btn btn-sm p-0' id='alert-x-close'>
            <i class='bi bi-x fs-3'></i>
          </span>
        </div>
        <div class='card-body overflow-x-hidden overflow-y-scroll p-2 text-center' style='max-height:200px'>
          ${text}
        </div>
        <div class='card-footer d-flex justify-content-end align-items-center px-2 py-1 border-0'>
          <button type='button' class='btn btn-primary btn-sm' id='alert-ok'>OK</button>
        </div>
      </div>
    </div>
  `;

  modalshow.appendChild(alert);

  document.querySelector("#alert-ok").addEventListener("click", () => {
    removeFromBody();
  });

  document.querySelector("#alert-x-close").addEventListener("click", () => {
    removeFromBody();
  });

  function removeFromBody() {
    modalshow.removeChild(alert);
  }
}
