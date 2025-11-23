class Exporter {
  constructor(data) {
    if (!Array.isArray(data)) throw new Error("Data must be an array");
    this.data = data;
  }

  // columns: array of keys
  // formatters: { key: fn(value) }
  // headers: { key: headerName }
  toRows(columns, formatters = {}) {
    return this.data.map((row) =>
      columns.map((col) => {
        const value = row[col] !== undefined && row[col] !== null
          ? row[col]
          : null;
        if (value === null) return "";
        return col in formatters ? formatters[col](value) : value;
      })
    );
  }

  getHeaders(columns, headers = {}) {
    return columns.map((col) => headers[col] || col);
  }
}

class CSVExporter extends Exporter {
  format_data(
    { columns, formatters = {}, headers = {} },
  ) {
    const rows = this.toRows(columns, formatters);
    const escape = (v) => `"${String(v).replace(/"/g, '""')}"`;
    const csvRows = [
      this.getHeaders(columns, headers).join(","),
      ...rows.map((r) => r.map(escape).join(",")),
    ];
    return csvRows.join("\n");
  }

  export(
    { columns, formatters = {}, headers = {}, filename = "export.csv" },
  ) {
    const csv = this.format_data({ columns, formatters, headers, filename });
    // BOM para UTF-8
    const BOM = "\uFEFF";
    const blob = new Blob([BOM + csv], { type: "text/csv;charset=utf-8;" });

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

class PDFExporter extends Exporter {
  async ensurePDFLibs() {
    const needJsPDF = !(globalThis.jspdf && globalThis.jspdf.jsPDF);
    const needAutoTable = !(globalThis.jspdf && globalThis.jspdf.jsPDF &&
      globalThis.jspdf.jsPDF.API && globalThis.jspdf.jsPDF.API.autoTable);

    const load = (src) =>
      new Promise((resolve, reject) => {
        const s = document.createElement("script");
        s.src = src;
        s.async = true;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
      });

    const tasks = [];
    if (needJsPDF) {
      tasks.push(
        load("https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"),
      );
    }
    await Promise.all(tasks);
    if (needAutoTable) {
      await load(
        "https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.1/dist/jspdf.plugin.autotable.min.js",
      );
    }
  }

  async exportPDF(
    {
      title,
      subtitle,
      columns,
      formatters = {},
      headers = {},
      filename = "export.pdf",
      tableOptions = {},
    },
  ) {
    await this.ensurePDFLibs();
    const { jsPDF } = globalThis.jspdf;
    const doc = new jsPDF({
      unit: tableOptions.unit || "pt",
      format: tableOptions.format || "letter",
      orientation: tableOptions.orientation || "portrait",
      compress: true,
    });

    doc.setFontSize(16);
    doc.text(title, 40, 40);
    if (subtitle) {
      doc.setFontSize(10);
      doc.text(subtitle, 40, 58);
    }

    const rows = this.toRows(columns, formatters);
    const tableHeaders = [this.getHeaders(columns, headers)];

    let columnStyles = {};
    if (tableOptions.columnStyles) {
      const nameToIdx = Object.fromEntries(columns.map((k, i) => [k, i]));
      for (const [key, val] of Object.entries(tableOptions.columnStyles)) {
        if (nameToIdx[key] !== undefined) columnStyles[nameToIdx[key]] = val;
      }
    }

    if (rows.length === 0) {
      doc.setFontSize(12);
      doc.text("Sin actividad en este periodo", 40, 110);
    } else {
      doc.autoTable({
        startY: 100,
        head: tableHeaders,
        body: rows,
        tableWidth: tableOptions.tableWidth || "auto",
        rowPageBreak: tableOptions.rowPageBreak || "auto",
        margin: tableOptions.margin ||
          { left: 32, right: 32, top: 90, bottom: 40 },
        styles: {
          fontSize: 9,
          cellPadding: 3,
          overflow: "linebreak",
          ...(tableOptions.styles || {}),
        },
        headStyles: {
          fillColor: [242, 242, 242],
          textColor: 0,
          ...(tableOptions.headStyles || {}),
        },
        columnStyles,
        theme: "grid",
        ...(tableOptions.autoTable || {}),
      });
    }

    doc.save(filename);
  }
}
