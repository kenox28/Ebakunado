(function (global) {
  function normalizeColsConfig(cols) {
    if (typeof cols === "number") {
      const count = cols;
      return Array.from({ length: count }).map((_, idx) => ({
        type: "text",
        widthClass: `skeleton-col-${Math.min(idx + 1, 6)}`,
      }));
    }
    if (Array.isArray(cols)) {
      return cols.map((c) => {
        if (typeof c === "string") return { type: c, widthClass: "" };
        return {
          type: c.type || "text",
          widthClass: c.widthClass || "",
        };
      });
    }
    return [];
  }

  function classForType(type) {
    switch (type) {
      case "pill":
        return "skeleton skeleton-pill";
      case "btn":
        return "skeleton skeleton-btn";
      case "avatar":
        return "skeleton skeleton-avatar";
      case "text":
      default:
        return "skeleton skeleton-text";
    }
  }

  /**
   * Generate skeleton rows HTML
   * @param {number|Array<{type:string,widthClass?:string}|string>} colsConfig Number of columns or array config per column
   * @param {number} rowCount Number of rows to render
   * @returns {string}
   */
  function generateSkeletonRowsHTML(colsConfig, rowCount = 10) {
    const cols = normalizeColsConfig(colsConfig);
    const tdTemplates = cols
      .map((c) => {
        const base = classForType(c.type);
        const width = c.widthClass ? ` ${c.widthClass}` : "";
        return `<td><div class="${base}${width}"></div></td>`;
      })
      .join("");
    let rows = "";
    for (let i = 0; i < rowCount; i++) {
      rows += `<tr class="skeleton-row">${tdTemplates}</tr>`;
    }
    return rows;
  }

  /**
   * Apply skeleton rows to a table body
   * @param {string|HTMLElement} tbodyOrSelector table body element or selector
   * @param {number|Array<{type:string,widthClass?:string}|string>} colsConfig columns configuration
   * @param {number} rowCount number of rows
   */
  function applyTableSkeleton(tbodyOrSelector, colsConfig, rowCount = 10) {
    const tbody =
      typeof tbodyOrSelector === "string"
        ? document.querySelector(tbodyOrSelector)
        : tbodyOrSelector;
    if (!tbody) return;
    tbody.innerHTML = generateSkeletonRowsHTML(colsConfig, rowCount);
  }

  /**
   * Render a standardized message row (empty/error/info)
   * @param {string|HTMLElement} tbodyOrSelector table body element or selector
   * @param {string} message The message text to display
   * @param {{ colspan?: number, kind?: 'info'|'error' }} [opts]
   */
  function renderTableMessage(tbodyOrSelector, message, opts = {}) {
    const tbody =
      typeof tbodyOrSelector === "string"
        ? document.querySelector(tbodyOrSelector)
        : tbodyOrSelector;
    if (!tbody) return;
    const colspan = Number.isFinite(opts.colspan) ? opts.colspan : 1;
    const isError = (opts.kind || "").toLowerCase() === "error";
    const cls = isError ? "message-row error" : "message-row";
    tbody.innerHTML = `<tr class="${cls}"><td colspan="${colspan}">${message}</td></tr>`;
  }

  // Expose globally
  global.generateSkeletonRowsHTML = generateSkeletonRowsHTML;
  global.applyTableSkeleton = applyTableSkeleton;
  global.renderTableMessage = renderTableMessage;

  // ==============================
  // Card/List skeleton generators
  // ==============================
  /**
   * Generate skeleton cards for children list
   * @param {number} count number of cards
   * @returns {string}
   */
  function generateChildrenListSkeleton(count = 4) {
    let html = "";
    for (let i = 0; i < count; i++) {
      html += `
      <div class="child-list-item skeleton-card">
        <div class="child-item-body">
          <div class="child-details">
            <div class="skeleton skeleton-text skeleton-w-60"></div>
            <div class="skeleton skeleton-text skeleton-w-40"></div>
            <div class="skeleton skeleton-text skeleton-w-30"></div>
          </div>
          <div class="qr-area">
            <div class="skeleton skeleton-avatar"></div>
          </div>
        </div>
        <div class="child-actions">
          <div class="skeleton skeleton-btn skeleton-btn-view"></div>
          <div class="skeleton skeleton-btn skeleton-btn-schedule"></div>
        </div>
      </div>`;
    }
    return html;
  }

  /**
   * Apply skeleton cards into a container for children list
   * @param {string|HTMLElement} containerOrSelector
   * @param {number} count
   * @param {string} [label] optional label HTML/text to prepend
   */
  function applyChildrenListSkeleton(
    containerOrSelector,
    count = 4,
    label = ""
  ) {
    const el =
      typeof containerOrSelector === "string"
        ? document.querySelector(containerOrSelector)
        : containerOrSelector;
    if (!el) return;
    const labelHtml = label
      ? `<div class="children-list-label">${label}</div>`
      : "";
    el.innerHTML = `${labelHtml}${generateChildrenListSkeleton(count)}`;
  }

  global.generateChildrenListSkeleton = generateChildrenListSkeleton;
  global.applyChildrenListSkeleton = applyChildrenListSkeleton;

  // ==============================================
  // Inline span (value placeholder) skeleton API
  // ==============================================
  const SPAN_SKELETON_WIDTH_CLASSES = [
    "skeleton-col-1",
    "skeleton-col-2",
    "skeleton-col-3",
    "skeleton-col-4",
    "skeleton-col-5",
    "skeleton-col-6",
    "skeleton-field-xs",
    "skeleton-field-s",
    "skeleton-field-m",
    "skeleton-field-l",
    "skeleton-field-xl",
    "skeleton-field-tick",
  ];

  /**
   * Apply a skeleton loading bar to a span (or any inline element) by id
   * @param {string} id element id
   * @param {string} widthClass one of skeleton-col-* or any width utility class
   */
  function applyFieldSkeleton(id, widthClass = "skeleton-col-2") {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = "";
    el.dataset.skeletonApplied = "true";
    el.classList.add("skeleton", "skeleton-text");
    if (widthClass) el.classList.add(widthClass);
  }

  /**
   * Remove skeleton from a field and set its text content.
   * Falls back to '-' when value is null/undefined/empty string.
   * @param {string} id element id
   * @param {any} value value to display
   */
  function setFieldValue(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove(
      "skeleton",
      "skeleton-text",
      ...SPAN_SKELETON_WIDTH_CLASSES
    );
    if (el.dataset && el.dataset.skeletonApplied)
      delete el.dataset.skeletonApplied;
    const v =
      value === null ||
      value === undefined ||
      (typeof value === "string" && value.trim() === "")
        ? "-"
        : value;
    el.textContent = v;
  }

  /**
   * Batch apply skeleton bars to multiple field ids.
   * @param {Record<string,string>} mapping key=id, value=widthClass
   */
  function applyFieldsSkeleton(mapping) {
    Object.keys(mapping || {}).forEach((id) =>
      applyFieldSkeleton(id, mapping[id])
    );
  }

  /**
   * Batch populate fields and remove their skeleton.
   * @param {Record<string,any>} values key=id, value=data
   */
  function setFieldsValues(values) {
    Object.keys(values || {}).forEach((id) => setFieldValue(id, values[id]));
  }

  // Expose global API
  global.applyFieldSkeleton = applyFieldSkeleton;
  global.setFieldValue = setFieldValue;
  global.applyFieldsSkeleton = applyFieldsSkeleton;
  global.setFieldsValues = setFieldsValues;

  // ==============================================
  // Predefined field groups for CHR sections
  // ==============================================
  const CHILD_INFO_FIELDS = {
    // column 1
    f_name: "skeleton-field-l",
    f_gender: "skeleton-field-s",
    f_birth_date: "skeleton-field-m",
    f_birth_place: "skeleton-field-l",
    f_birth_weight: "skeleton-field-s",
    f_birth_height: "skeleton-field-s",
    f_address: "skeleton-field-xl",
    f_allergies: "skeleton-field-l",
    f_blood_type: "skeleton-field-s",
    // column 2
    f_family_no: "skeleton-field-m",
    f_lpm: "skeleton-field-m",
    f_philhealth: "skeleton-field-m",
    f_nhts: "skeleton-field-s",
    f_non_nhts: "skeleton-field-s",
    f_father: "skeleton-field-l",
    f_mother: "skeleton-field-l",
    f_nb_screen: "skeleton-field-m",
    f_fp: "skeleton-field-m",
  };

  const CHILD_HISTORY_FIELDS = {
    f_nbs_date: "skeleton-field-m",
    f_delivery_type: "skeleton-field-m",
    f_birth_order: "skeleton-field-s",
    f_nbs_place: "skeleton-field-l",
    f_attended_by: "skeleton-field-l",
  };

  const FEEDING_FIELDS = {
    // Exclusive breastfeeding ticks (1-6 months)
    f_eb_1mo: "skeleton-field-tick",
    f_eb_2mo: "skeleton-field-tick",
    f_eb_3mo: "skeleton-field-tick",
    f_eb_4mo: "skeleton-field-tick",
    f_eb_5mo: "skeleton-field-tick",
    f_eb_6mo: "skeleton-field-tick",
    // Complementary feeding notes
    f_cf_6mo: "skeleton-field-m",
    f_cf_7mo: "skeleton-field-m",
    f_cf_8mo: "skeleton-field-m",
  };

  const TD_STATUS_FIELDS = {
    f_td_dose1: "skeleton-field-m",
    f_td_dose2: "skeleton-field-m",
    f_td_dose3: "skeleton-field-m",
    f_td_dose4: "skeleton-field-m",
    f_td_dose5: "skeleton-field-m",
  };

  function applyChrSkeletons() {
    applyFieldsSkeleton({
      ...CHILD_INFO_FIELDS,
      ...CHILD_HISTORY_FIELDS,
      ...FEEDING_FIELDS,
      ...TD_STATUS_FIELDS,
    });
  }

  // Export group maps and helper
  global.CHR_SKELETON = {
    CHILD_INFO_FIELDS,
    CHILD_HISTORY_FIELDS,
    FEEDING_FIELDS,
    TD_STATUS_FIELDS,
    apply: applyChrSkeletons,
  };

  // ==============================================
  // Dashboard card number skeletons
  // ==============================================
  /**
   * Apply skeleton shimmer to all elements with .card-number
   * Wraps their content with a placeholder block that matches height via .skeleton-number
   */
  function applyDashboardCardNumbersSkeleton() {
    const els = document.querySelectorAll(".card-number");
    els.forEach((el) => {
      if (el.dataset.cardSkeletonApplied === "true") return;
      const valueText = el.textContent.trim();
      // Structure for cross-fade if desired by CSS
      const valueSpan = document.createElement("span");
      valueSpan.className = "number-value";
      valueSpan.textContent = valueText || "0";

      const placeholder = document.createElement("span");
      placeholder.className = "skeleton-placeholder";
      const bar = document.createElement("div");
      bar.className = "skeleton skeleton-number";
      // approximate width to 2ch initially; CSS can override responsiveness
      bar.style.width = "48px";
      placeholder.appendChild(bar);

      el.innerHTML = "";
      el.appendChild(valueSpan);
      el.appendChild(placeholder);
      el.classList.add("is-loading");
      el.dataset.cardSkeletonApplied = "true";
    });
  }

  /**
   * Set card number values and remove skeleton loading state
   * @param {Record<string, string|number>} map keys are element ids, values are numbers
   */
  function setDashboardCardNumbers(map) {
    Object.keys(map || {}).forEach((id) => {
      const el = document.getElementById(id);
      if (!el) return;
      const valSpan = el.querySelector(".number-value");
      if (valSpan) valSpan.textContent = String(map[id] ?? "0");
      el.classList.remove("is-loading");
      el.classList.add("is-loaded");
    });
  }

  global.applyDashboardCardNumbersSkeleton = applyDashboardCardNumbersSkeleton;
  global.setDashboardCardNumbers = setDashboardCardNumbers;
})(window);
