function SwalMessage({ title = "", text = "", icon = "info" }) {
  const SwalWithCustomClass = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-primary",
    },
    buttonsStyling: false,
  });

  SwalWithCustomClass.fire({
    title: title,
    text: text,
    icon: icon,
  });
}

async function SwalConfirm(
  {
    title = "",
    text = "",
    icon = "warning",
    confirmButtonText = "Yes",
    cancelButtonText = "No",
    showCancelButton = true,
    reverseButtons = true,
  },
) {
  const SwalWithCustomClass = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-primary m-16",
      cancelButton: "btn btn-secondary",
    },
    buttonsStyling: false,
  });

  const response = await SwalWithCustomClass.fire({
    title: title,
    text: text,
    icon: icon,
    showCancelButton: showCancelButton,
    confirmButtonText: confirmButtonText,
    cancelButtonText: cancelButtonText,
    reverseButtons: reverseButtons,
  });
  return response.isConfirmed;
}

async function SwalInput(
  {
    title = "",
    inputLabel = "",
    inputPlaceholder = "",
    inputValue = "",
    inputType = "text",
    confirmButtonText = "Submit",
    cancelButtonText = "Cancel",
  },
) {
  const SwalWithCustomClass = Swal.mixin({
    customClass: {
      confirmButton: "btn btn-primary m-16",
      cancelButton: "btn btn-secondary",
      input: "form-control-modal",
    },
    buttonsStyling: false,
  });

  const response = await SwalWithCustomClass.fire({
    title: title,
    input: inputType,
    inputLabel: inputLabel,
    inputPlaceholder: inputPlaceholder,
    inputValue: inputValue,
    showCancelButton: true,
    confirmButtonText: confirmButtonText,
    cancelButtonText: cancelButtonText,
    reverseButtons: true,
  });
  if (response.isConfirmed) {
    return response.value;
  } else {
    return null;
  }
}

// Example usage:
//SwalMessage("Hello!", "This is a custom message.", "success");
/*(async () => {
  const userConfirmed = await SwalConfirm({
    title: "Are you sure?",
    text: "You won't be able to revert this!",
    icon: "warning",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "No, cancel!",
  });
  console.log("User confirmed:", userConfirmed);
})();
*/
