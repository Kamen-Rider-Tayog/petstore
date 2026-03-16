// Appointment booking JavaScript helpers

function initAppointmentBooking() {
  const dateInput = document.querySelector("#appointment_date");
  const slotContainer = document.querySelector("#availableSlots");

  if (!dateInput || !slotContainer) return;

  dateInput.addEventListener("change", () => {
    const date = dateInput.value;
    if (!date) return;
    loadAvailableSlots(date, slotContainer);
  });
}

function loadAvailableSlots(date, container) {
  const serviceId = container.dataset.serviceId;
  if (!serviceId) return;

  container.innerHTML = "<p>Loading available slots…</p>";

  fetch(
    `/petstore/backend/api/get_available_slots.php?service_id=${serviceId}&date=${encodeURIComponent(date)}`,
  )
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        container.innerHTML = "<p>No slots available.</p>";
        return;
      }

      if (!data.slots || !data.slots.length) {
        container.innerHTML =
          "<p>No slots available for the selected date.</p>";
        return;
      }

      container.innerHTML = "";
      data.slots.forEach((slot) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "btn";
        btn.textContent = slot.time;
        btn.style.margin = "4px";
        btn.dataset.time = slot.time;
        btn.dataset.employees = JSON.stringify(slot.employees);
        btn.addEventListener("click", () => selectSlot(btn));
        container.appendChild(btn);
      });
    })
    .catch(() => {
      container.innerHTML = "<p>Error loading slots. Please try again.</p>";
    });
}

let selectedSlotButton = null;

function selectSlot(button) {
  if (selectedSlotButton) {
    selectedSlotButton.classList.remove("selected-slot");
  }
  button.classList.add("selected-slot");
  selectedSlotButton = button;

  const time = button.dataset.time;
  const employees = JSON.parse(button.dataset.employees || "[]");

  const timeInput = document.querySelector("#appointment_time");
  const employeeInput = document.querySelector("#appointment_employee");

  if (timeInput) {
    timeInput.value = time;
  }
  if (employeeInput) {
    // Choose first available employee by default
    if (employees.length > 0) {
      employeeInput.value = employees[0];
    }
  }
}

window.addEventListener("DOMContentLoaded", initAppointmentBooking);
