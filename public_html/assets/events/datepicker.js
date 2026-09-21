/**
 * Figma-Style Persian Jalali Date & Time Picker
 * Standalone, lightweight, zero external dependencies.
 */
(function (global) {
  'use strict';

  const MONTH_NAMES = [
    'فروردین', 'اردیبهشت', 'خرداد',
    'تیر', 'مرداد', 'شهریور',
    'مهر', 'آبان', 'آذر',
    'دی', 'بهمن', 'اسفند'
  ];

  const WEEK_DAYS = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];

  function toFaDigits(str) {
    if (str === null || str === undefined) return '';
    return String(str).replace(/[0-9]/g, function (d) {
      return '۰۱۲۳۴۵۶۷۸۹'[d];
    });
  }

  function toEnDigits(str) {
    if (!str) return '';
    return String(str).replace(/[۰-۹]/g, function (d) {
      return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d);
    });
  }

  function padZero(num) {
    return String(num).padStart(2, '0');
  }

  // Exact Jalali <-> Gregorian conversion
  function jalaliToGregorian(jy, jm, jd) {
    let gy = jy > 979 ? 1600 : 621;
    jy -= jy > 979 ? 979 : 0;
    let days = (365 * jy) + (Math.floor(jy / 33) * 8) + Math.floor(((jy % 33) + 3) / 4) + 78 + jd + (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
    gy += 400 * Math.floor(days / 146097);
    days %= 146097;
    if (days > 36524) {
      gy += 100 * Math.floor(--days / 36524);
      days %= 36524;
      if (days >= 365) days++;
    }
    gy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
      gy += Math.floor((days - 1) / 365);
      days = (days - 1) % 365;
    }
    let gd = days + 1;
    const gmd = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    let gm = 0;
    while (gm < 12 && gd > gmd[gm]) {
      gd -= gmd[gm];
      gm++;
    }
    return { gy: gy, gm: gm, gd: gd };
  }

  function gregorianToJalali(gy, gm, gd) {
    const gdm = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    const gy2 = gm > 2 ? gy + 1 : gy;
    let days = 355666 + (365 * gy) + Math.floor((gy2 + 3) / 4) - Math.floor((gy2 + 99) / 100) + Math.floor((gy2 + 399) / 400) + gd + gdm[gm - 1];
    let jy = -1595 + (33 * Math.floor(days / 12053));
    days %= 12053;
    jy += 4 * Math.floor(days / 1461);
    days %= 1461;
    if (days > 365) {
      jy += Math.floor((days - 1) / 365);
      days = (days - 1) % 365;
    }
    return {
      jy: jy,
      jm: days < 186 ? 1 + Math.floor(days / 31) : 7 + Math.floor((days - 186) / 30),
      jd: days < 186 ? 1 + (days % 31) : 1 + ((days - 186) % 30)
    };
  }

  function isJalaliLeapYear(jy) {
    return ((((jy + 38) * 682) % 2816) < 682);
  }

  function getMonthDays(jy, jm) {
    if (jm <= 6) return 31;
    if (jm <= 11) return 30;
    return isJalaliLeapYear(jy) ? 30 : 29;
  }

  // Saturday = 0, Sunday = 1, ..., Friday = 6
  function getFirstDayOfWeek(jy, jm) {
    const g = jalaliToGregorian(jy, jm, 1);
    const date = new Date(Date.UTC(g.gy, g.gm - 1, g.gd));
    return (date.getUTCDay() + 1) % 7;
  }

  class PersianDatePicker {
    constructor(wrapperEl, options) {
      this.wrapper = wrapperEl;
      this.options = Object.assign({
        defaultDate: '1405/07/16',
        defaultTime: '08:00',
        dateInputName: 'jalali_date',
        timeInputName: 'start_time',
        onChange: null
      }, options);

      // Find or create hidden inputs
      this.dateInput = this.wrapper.querySelector(`input[name="${this.options.dateInputName}"]`);
      if (!this.dateInput) {
        this.dateInput = document.createElement('input');
        this.dateInput.type = 'hidden';
        this.dateInput.name = this.options.dateInputName;
        this.wrapper.appendChild(this.dateInput);
      } else {
        this.dateInput.type = 'hidden';
      }

      this.timeInput = this.wrapper.querySelector(`input[name="${this.options.timeInputName}"]`);
      if (!this.timeInput) {
        this.timeInput = document.createElement('input');
        this.timeInput.type = 'hidden';
        this.timeInput.name = this.options.timeInputName;
        this.wrapper.appendChild(this.timeInput);
      } else {
        this.timeInput.type = 'hidden';
      }

      // Initial state
      const initialDateStr = toEnDigits(this.dateInput.value || this.options.defaultDate);
      const dateParts = initialDateStr.split(/[\/\-]/).map(Number);
      
      const initialTimeStr = toEnDigits(this.timeInput.value || this.options.defaultTime);
      const timeParts = initialTimeStr.split(':').map(Number);

      const now = new Date();
      this.today = gregorianToJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());

      this.state = {
        year: dateParts[0] || 1405,
        month: dateParts[1] || 7,
        day: dateParts[2] || 16,
        hour: !isNaN(timeParts[0]) ? timeParts[0] : 8,
        minute: !isNaN(timeParts[1]) ? timeParts[1] : 0,
        view: 'days', // 'days', 'months', 'years', 'time'
        isOpen: false
      };

      this.buildDOM();
      this.bindEvents();
      this.syncInputs();
      this.render();
    }

    buildDOM() {
      this.wrapper.classList.add('pdp-wrapper');

      // Trigger button
      this.trigger = document.createElement('button');
      this.trigger.type = 'button';
      this.trigger.className = 'pdp-trigger';
      this.trigger.setAttribute('aria-haspopup', 'dialog');
      this.trigger.setAttribute('aria-expanded', 'false');

      this.triggerVal = document.createElement('span');
      this.triggerVal.className = 'pdp-trigger-val';

      const iconsWrap = document.createElement('div');
      iconsWrap.className = 'pdp-trigger-icons';
      iconsWrap.innerHTML = `
        <svg class="pdp-cal-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="4" rx="2"></rect>
          <path d="M8 2v4"></path>
          <path d="M16 2v4"></path>
          <path d="M3 10h18"></path>
        </svg>
        <div class="pdp-clock-badge">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
      `;

      this.trigger.append(this.triggerVal, iconsWrap);
      this.wrapper.appendChild(this.trigger);

      // Popover
      this.popover = document.createElement('div');
      this.popover.className = 'pdp-popover';
      this.popover.setAttribute('role', 'dialog');

      this.popover.innerHTML = `
        <div class="pdp-header">
          <button type="button" class="pdp-nav-btn pdp-nav-prev" title="ماه بعد">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="m9 18 6-6-6-6"></path>
            </svg>
          </button>
          <div class="pdp-title-group">
            <button type="button" class="pdp-title-btn pdp-title-month"></button>
            <button type="button" class="pdp-title-btn pdp-title-year"></button>
          </div>
          <button type="button" class="pdp-nav-btn pdp-nav-next" title="ماه قبل">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="m15 18-6-6 6-6"></path>
            </svg>
          </button>
        </div>

        <div class="pdp-view pdp-view-days is-active">
          <div class="pdp-weekdays">
            ${WEEK_DAYS.map(w => `<div class="pdp-weekday">${w}</div>`).join('')}
          </div>
          <div class="pdp-days-grid"></div>
        </div>

        <div class="pdp-view pdp-view-months">
          <div class="pdp-months-grid">
            ${MONTH_NAMES.map((m, i) => `<button type="button" class="pdp-month-btn" data-month="${i + 1}">${m}</button>`).join('')}
          </div>
        </div>

        <div class="pdp-view pdp-view-years">
          <div class="pdp-years-grid"></div>
        </div>

        <div class="pdp-view pdp-view-time">
          <div class="pdp-time-panel">
            <div class="pdp-time-heading">تنظیم ساعت و دقیقه رویداد</div>
            <div class="pdp-time-spinners">
              <div class="pdp-time-stepper">
                <button type="button" class="pdp-time-step-btn" data-step="hour" data-dir="1">▲</button>
                <div class="pdp-time-val pdp-hour-val">۰۸</div>
                <button type="button" class="pdp-time-step-btn" data-step="hour" data-dir="-1">▼</button>
              </div>
              <span class="pdp-time-sep">:</span>
              <div class="pdp-time-stepper">
                <button type="button" class="pdp-time-step-btn" data-step="minute" data-dir="1">▲</button>
                <div class="pdp-time-val pdp-min-val">۰۰</div>
                <button type="button" class="pdp-time-step-btn" data-step="minute" data-dir="-1">▼</button>
              </div>
            </div>
            <button type="button" class="pdp-time-confirm-btn">تایید زمان و بازگشت</button>
          </div>
        </div>

        <div class="pdp-footer">
          <div class="pdp-footer-actions">
            <button type="button" class="pdp-btn-text pdp-btn-today">امروز</button>
            <button type="button" class="pdp-btn-text pdp-btn-toggle-time">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
              <span>تنظیم ساعت</span>
            </button>
          </div>
          <button type="button" class="pdp-btn-text pdp-btn-clear">پاک کردن</button>
        </div>
      `;

      this.wrapper.appendChild(this.popover);

      // Elements cache
      this.titleMonth = this.popover.querySelector('.pdp-title-month');
      this.titleYear = this.popover.querySelector('.pdp-title-year');
      this.daysGrid = this.popover.querySelector('.pdp-days-grid');
      this.monthsGrid = this.popover.querySelector('.pdp-months-grid');
      this.yearsGrid = this.popover.querySelector('.pdp-years-grid');
      this.hourVal = this.popover.querySelector('.pdp-hour-val');
      this.minVal = this.popover.querySelector('.pdp-min-val');
      this.toggleTimeBtn = this.popover.querySelector('.pdp-btn-toggle-time');
    }

    bindEvents() {
      // Toggle popover
      this.trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        this.toggle();
      });

      // Outside click close
      document.addEventListener('click', (e) => {
        if (this.state.isOpen && !this.wrapper.contains(e.target)) {
          this.close();
        }
      });

      // Escape close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.state.isOpen) {
          this.close();
        }
      });

      // Navigation buttons
      this.popover.querySelector('.pdp-nav-prev').addEventListener('click', () => this.changeMonth(1));
      this.popover.querySelector('.pdp-nav-next').addEventListener('click', () => this.changeMonth(-1));

      // Title buttons (switch views)
      this.titleMonth.addEventListener('click', () => {
        this.setView(this.state.view === 'months' ? 'days' : 'months');
      });
      this.titleYear.addEventListener('click', () => {
        this.setView(this.state.view === 'years' ? 'days' : 'years');
      });

      // Month select
      this.monthsGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('.pdp-month-btn');
        if (!btn) return;
        this.state.month = Number(btn.dataset.month);
        this.validateDay();
        this.setView('days');
        this.syncInputs();
        this.render();
      });

      // Year select
      this.yearsGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('.pdp-year-btn');
        if (!btn) return;
        this.state.year = Number(btn.dataset.year);
        this.validateDay();
        this.setView('days');
        this.syncInputs();
        this.render();
      });

      // Time stepper buttons
      this.popover.querySelectorAll('.pdp-time-step-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const stepType = btn.dataset.step;
          const dir = Number(btn.dataset.dir);
          if (stepType === 'hour') {
            this.state.hour = (this.state.hour + dir + 24) % 24;
          } else {
            this.state.minute = (this.state.minute + (dir * 5) + 60) % 60;
          }
          this.syncInputs();
          this.renderTime();
        });
      });

      // Confirm time button
      this.popover.querySelector('.pdp-time-confirm-btn').addEventListener('click', () => {
        this.setView('days');
      });

      // Footer actions
      this.popover.querySelector('.pdp-btn-today').addEventListener('click', () => {
        this.state.year = this.today.jy;
        this.state.month = this.today.jm;
        this.state.day = this.today.jd;
        this.setView('days');
        this.syncInputs();
        this.render();
      });

      this.toggleTimeBtn.addEventListener('click', () => {
        this.setView(this.state.view === 'time' ? 'days' : 'time');
      });

      this.popover.querySelector('.pdp-btn-clear').addEventListener('click', () => {
        this.dateInput.value = '';
        this.timeInput.value = '';
        this.triggerVal.textContent = 'انتخاب تاریخ و زمان رویداد';
        this.triggerVal.classList.add('is-placeholder');
        this.close();
      });
    }

    validateDay() {
      const maxDays = getMonthDays(this.state.year, this.state.month);
      if (this.state.day > maxDays) {
        this.state.day = maxDays;
      }
    }

    changeMonth(dir) {
      this.state.month += dir;
      if (this.state.month > 12) {
        this.state.month = 1;
        this.state.year++;
      } else if (this.state.month < 1) {
        this.state.month = 12;
        this.state.year--;
      }
      this.validateDay();
      this.syncInputs();
      this.render();
    }

    setView(viewName) {
      this.state.view = viewName;
      this.popover.querySelectorAll('.pdp-view').forEach(el => el.classList.remove('is-active'));
      const activeEl = this.popover.querySelector(`.pdp-view-${viewName}`);
      if (activeEl) activeEl.classList.add('is-active');

      this.titleMonth.classList.toggle('is-active', viewName === 'months');
      this.titleYear.classList.toggle('is-active', viewName === 'years');

      const timeBtnSpan = this.toggleTimeBtn.querySelector('span');
      if (timeBtnSpan) {
        timeBtnSpan.textContent = viewName === 'time' ? 'مشاهده تقویم' : 'تنظیم ساعت';
      }

      this.render();
    }

    toggle() {
      if (this.state.isOpen) this.close();
      else this.open();
    }

    open() {
      this.state.isOpen = true;
      this.popover.classList.add('is-open');
      this.trigger.setAttribute('aria-expanded', 'true');
      this.render();
    }

    close() {
      this.state.isOpen = false;
      this.popover.classList.remove('is-open');
      this.trigger.setAttribute('aria-expanded', 'false');
      this.setView('days');
    }

    syncInputs() {
      const dateStr = `${this.state.year}/${padZero(this.state.month)}/${padZero(this.state.day)}`;
      const timeStr = `${padZero(this.state.hour)}:${padZero(this.state.minute)}`;

      this.dateInput.value = dateStr;
      this.timeInput.value = timeStr;

      const formattedLabel = `${toFaDigits(this.state.year)}/${toFaDigits(padZero(this.state.month))}/${toFaDigits(padZero(this.state.day))} - ${toFaDigits(padZero(this.state.hour))}:${toFaDigits(padZero(this.state.minute))}`;
      this.triggerVal.textContent = formattedLabel;
      this.triggerVal.classList.remove('is-placeholder');

      // Dispatch change event
      this.dateInput.dispatchEvent(new Event('change', { bubbles: true }));
      this.timeInput.dispatchEvent(new Event('change', { bubbles: true }));

      if (typeof this.options.onChange === 'function') {
        this.options.onChange({
          jalaliDate: dateStr,
          time: timeStr,
          year: this.state.year,
          month: this.state.month,
          day: this.state.day,
          hour: this.state.hour,
          minute: this.state.minute
        });
      }
    }

    render() {
      // Titles
      this.titleMonth.textContent = MONTH_NAMES[this.state.month - 1];
      this.titleYear.textContent = toFaDigits(this.state.year);

      if (this.state.view === 'days') {
        this.renderDays();
      } else if (this.state.view === 'months') {
        this.renderMonths();
      } else if (this.state.view === 'years') {
        this.renderYears();
      } else if (this.state.view === 'time') {
        this.renderTime();
      }
    }

    renderDays() {
      this.daysGrid.innerHTML = '';
      const firstDayOffset = getFirstDayOfWeek(this.state.year, this.state.month);
      const totalDays = getMonthDays(this.state.year, this.state.month);

      // Empty leading days
      for (let i = 0; i < firstDayOffset; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'pdp-day-cell';
        this.daysGrid.appendChild(emptyCell);
      }

      // Day buttons
      for (let d = 1; d <= totalDays; d++) {
        const cell = document.createElement('div');
        cell.className = 'pdp-day-cell';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pdp-day-btn';
        btn.textContent = toFaDigits(d);

        const isSelected = d === this.state.day;
        const isToday = this.state.year === this.today.jy && this.state.month === this.today.jm && d === this.today.jd;

        if (isSelected) btn.classList.add('is-selected');
        if (isToday) btn.classList.add('is-today');

        btn.addEventListener('click', () => {
          this.state.day = d;
          this.syncInputs();
          this.renderDays();
          // Optional smooth close on day click if already in days view
        });

        cell.appendChild(btn);
        this.daysGrid.appendChild(cell);
      }
    }

    renderMonths() {
      this.monthsGrid.querySelectorAll('.pdp-month-btn').forEach(btn => {
        const m = Number(btn.dataset.month);
        btn.classList.toggle('is-selected', m === this.state.month);
      });
    }

    renderYears() {
      this.yearsGrid.innerHTML = '';
      const startYear = Math.max(1300, this.state.year - 15);
      const endYear = Math.min(1500, this.state.year + 25);

      for (let y = startYear; y <= endYear; y++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pdp-year-btn';
        if (y === this.state.year) btn.classList.add('is-selected');
        btn.dataset.year = y;
        btn.textContent = toFaDigits(y);
        this.yearsGrid.appendChild(btn);
      }

      // Scroll selected into view
      setTimeout(() => {
        const sel = this.yearsGrid.querySelector('.is-selected');
        if (sel) sel.scrollIntoView({ block: 'center', behavior: 'smooth' });
      }, 50);
    }

    renderTime() {
      this.hourVal.textContent = toFaDigits(padZero(this.state.hour));
      this.minVal.textContent = toFaDigits(padZero(this.state.minute));
    }
  }

  // Auto-init on elements with data-persian-datepicker
  function autoInit() {
    document.querySelectorAll('[data-persian-datepicker]').forEach(el => {
      if (!el.__pdpInstance) {
        el.__pdpInstance = new PersianDatePicker(el);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
  } else {
    autoInit();
  }

  global.PersianDatePicker = PersianDatePicker;
})(typeof window !== 'undefined' ? window : this);
