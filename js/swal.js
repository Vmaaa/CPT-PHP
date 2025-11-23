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
    showCancelButton: true,
    confirmButtonText: confirmButtonText,
    cancelButtonText: cancelButtonText,
    reverseButtons: true,
  });
  return response.isConfirmed;
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
