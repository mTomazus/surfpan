window.initHeatDragDrop = function initHeatDragDrop(opts = {}) {
  const {
    listSelector = "#heat-list",
    cardSelector = ".heat-card",
    handleSelector = ".drag-handle",
    onReorder = null, // unused in your current version; keeping for compatibility
  } = opts;

  const list = document.querySelector(listSelector);
  if (!list) return false;

  if (list.dataset.dndInit === "1") return true;
  list.dataset.dndInit = "1";

  let dragEl = null;
  let placeholder = null;
  let startX = 0, startY = 0;

  // Tail bundle state
  let groupFollowers = [];
  let badgeEl = null;

  const makePlaceholder = (h, margin) => {
    const ph = document.createElement("div");
    ph.className = "heat-placeholder";
    ph.style.height = `${h}px`;
    ph.style.margin = margin;
    return ph;
  };

  function getCompId() {
    const wrap = document.querySelector("#competition-schedule-control");
    return wrap ? wrap.dataset.compId : null;
  }

  function commitOrder() {
    // Use the actual list + selectors, not hard-coded query
    const order = [...list.querySelectorAll(cardSelector)]
      .map(el => Number(el.dataset.heatId));

    const compId = getCompId();
    if (!compId) {
      console.warn("[DnD] comp_id not found on #competition-schedule-control");
      return;
    }

    fetch(`/surfpan/heats-schedules/reorder/${compId}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order })
    }).catch(err => console.warn("[DnD] reorder failed", err));
  }

  // Collect "tail" = this heat + consecutive same-division heats below
  function collectDivisionTail(headCard) {
    const divisionId = headCard.dataset.divisionId; // requires data-division-id
    if (!divisionId) return [headCard];

    const out = [headCard];
    let n = headCard.nextElementSibling;

    while (n) {
      if (!n.matches(cardSelector)) { n = n.nextElementSibling; continue; }

      // Stop tail when division changes
      if (n.dataset.divisionId !== divisionId) break;

      // Optional safety: stop tail at running/finished
      const st = n.dataset.status;
      if (st === "running" || st === "finished") break;

      out.push(n);
      n = n.nextElementSibling;
    }

    return out;
  }

  const findBeforeY = (y) => {
    const cards = [...list.querySelectorAll(`${cardSelector}:not(.dragging)`)];

    for (const c of cards) {
      const r = c.getBoundingClientRect();
      if (y < r.top + r.height / 2) return c;
    }
    return null;
  };

  const onMove = (e) => {
    if (!dragEl) return;
    e.preventDefault();

    const dx = e.clientX - startX;
    const dy = e.clientY - startY;
    dragEl.style.transform = `translate3d(${dx}px, ${dy}px, 0)`;

    const before = findBeforeY(e.clientY);
    if (before) list.insertBefore(placeholder, before);
    else list.appendChild(placeholder);
  };

  const cleanupBundleVisuals = () => {
    if (!dragEl) return;
    dragEl.classList.remove("stack");
    if (badgeEl) badgeEl.remove();
    badgeEl = null;
  };

  const onUp = () => {
    if (!dragEl) return;

    document.removeEventListener("pointermove", onMove, { passive: false });
    document.removeEventListener("pointerup", onUp);

    // Restore dragged head styles
    dragEl.classList.remove("dragging");
    dragEl.style.position = "";
    dragEl.style.left = "";
    dragEl.style.top = "";
    dragEl.style.width = "";
    dragEl.style.zIndex = "";
    dragEl.style.transform = "";

    // Insert head + followers at placeholder position
    list.insertBefore(dragEl, placeholder);
    groupFollowers.forEach(el => list.insertBefore(el, placeholder));

    cleanupBundleVisuals();

    placeholder.remove();
    placeholder = null;
    dragEl = null;
    groupFollowers = [];

    commitOrder();
  };

  // ✅ bind ONLY to handles
  const bindHandles = () => {
    list.querySelectorAll(handleSelector).forEach(handle => {
      handle.addEventListener("pointerdown", (e) => {
        e.preventDefault();
        e.stopPropagation();

        const card = handle.closest(cardSelector);
        if (!card) return;

        // Build tail group
        const group = collectDivisionTail(card);
        dragEl = group[0];
        groupFollowers = group.slice(1);

        // Total height placeholder to represent the whole block
        const totalHeight = group.reduce((sum, el) => sum + el.getBoundingClientRect().height, 0);

        // Insert placeholder AFTER the last element in the group (before removing followers)
        const last = group[group.length - 1];
        placeholder = makePlaceholder(totalHeight, getComputedStyle(dragEl).margin);
        list.insertBefore(placeholder, last.nextSibling);

        // Collapse: remove followers from DOM during drag (visual bunch)
        groupFollowers.forEach(el => el.remove());

        // Add "bundle" visuals if we have followers
        if (groupFollowers.length > 0) {
          dragEl.classList.add("stack");
          badgeEl = document.createElement("div");
          badgeEl.className = "stack-badge";
          badgeEl.textContent = `+${groupFollowers.length}`;
          dragEl.appendChild(badgeEl);
        } else {
          cleanupBundleVisuals();
        }

        // Lift head element
        const r = dragEl.getBoundingClientRect();
        startX = e.clientX;
        startY = e.clientY;

        dragEl.classList.add("dragging");
        dragEl.style.position = "fixed";
        dragEl.style.left = `${r.left}px`;
        dragEl.style.top = `${r.top}px`;
        dragEl.style.width = `${r.width}px`;
        dragEl.style.zIndex = "9999";
        dragEl.style.transform = "translate3d(0,0,0)";

        handle.setPointerCapture?.(e.pointerId);

        document.addEventListener("pointermove", onMove, { passive: false });
        document.addEventListener("pointerup", onUp);
      }, { passive: false });
    });
  };

  bindHandles();
  return true;
};
