const modalshow = document.querySelector("#modal-show");
const confirm = document.createElement("div");

export default function Confirm(text = "", callback) {
  confirm.setAttribute("id", "confirmbox");
  confirm.innerHTML = `
    <div class='position-fixed w-100 h-100 top-0 start-0' style='background: rgba(0, 0, 0, .5); z-index:100000'>
      <div class='card position-absolute top-50 start-50 translate-middle border' style='width:300px'>
        <div class='card-header d-flex justify-content-between align-items-center px-2 py-1 border-0'>
          <span class='d-flex align-items-center'>
            <i class="bi bi-globe"></i> <span class='ms-2'>${location.protocol}://${location.hostname}</span>
          </span>
          <span class='btn btn-sm p-0' id='confirm-x-close'>
            <i class='bi bi-x fs-3'></i>
          </span>
        </div>
        <div class='card-body overflow-x-hidden overflow-y-scroll p-2 text-center' style='max-height:200px'>${text}</div>
        <div class='card-footer d-flex justify-content-end align-items-center px-2 py-1 border-0'>
          <button type='button' class='btn btn-primary btn-sm' id='confirm-ok'>OK</button>
          <button type='button' class='btn btn-secondary btn-sm ms-1' id='confirm-close'>Cancel</button>
        </div>
      </div>
    </div>
  `;
  modalshow.appendChild(confirm);

  document.querySelector("#confirm-ok").addEventListener("click", () => {
    callback(true);
    removeFromBody();
  });

  document.querySelector("#confirm-close").addEventListener("click", () => {
    callback(false);
    removeFromBody();
  });

  document.querySelector("#confirm-x-close").addEventListener("click", () => {
    callback(false);
    removeFromBody();
  });

  function removeFromBody() {
    modalshow.removeChild(confirm);
  }
}
