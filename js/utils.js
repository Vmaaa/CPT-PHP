function formatDateForDatabase(d) {
  //format from "YYYY/MM/DD" to "YYYY-MM-DD
  if (!d) return null;
  const parts = d.split(/[-\/]/);
  if (parts.length !== 3) return null;
  const [year, month, day] = parts;
  return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
}

