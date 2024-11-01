const BASE_URL = "/wp-json/wiwi-websolutions-bookingsystem/v2";



/**
 * Wiwi Websolutions Bookingsystem fix issue with calendar not loading when placed into a tab
 */
// document.addEventListener('click', (e) => {
//     if (e.target.innerText == 'Boek dit vakantiehuis' || e.target.innerText == 'Beschikbaarheid') {
//         setTimeout(() => {
//             window.dispatchEvent(new Event('resize'), 100);
//         }, 500);
//     }
// });



//======================================================================
// Start of script, where dom is loaded
//======================================================================
document.addEventListener('DOMContentLoaded', function () {
    let events = [];

    let propEl = document.getElementById('property-id');
    let bookEl = document.getElementById('booking-wrapper');
    if (propEl == null && bookEl != null) {
        bookEl.classList.add('d-none');
    }

    if (propEl != null && bookEl != null) {

        let propId = propEl.value;
        let bookingCalendar = new BookingCalendar(propId);
        let bookingForm = new BookingForm(propId, bookingCalendar);
        bookingCalendar.subscribe(bookingForm);

        document.getElementById('reset-btn').addEventListener('click', () => {
            bookingCalendar.reset();
            bookingForm.reset();
        });
    }

    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function () {
        'use strict';

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation');

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                }, false);
            });
    })();

});



//======================================================================
// Helpers
//======================================================================
Date.prototype.addDays = function (days) {
    var date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
};

function loadLocalStorage(propertyId) {
    let data = localStorage.getItem(`booking-form-${propertyId}`);
    if (data == null) return null;
    data = JSON.parse(data);

    if (data.arrival_date == "" && data.departure_date == "") return null;

    if (moment().diff(moment(data.updated_at), 'hours') > 6) {
        localStorage.removeItem(`booking-form-${propertyId}`);
        localStorage.removeItem(`personal-data-${propertyId}`);
        return null;
    }
    return data;
}



//======================================================================
// BookingCalendar (before the wizard)
//======================================================================
class BookingCalendar {
    listeners = [];
    _onDateClick = (date) => { };
    _onValidBookingDate = ({ property, bookingEvent }) => { };

    constructor(propId) {
        this.init(propId)
    }

    async init(propId) {
        this.canInteract = true;
        this.propertyId = propId;
        this.bookingEvent = null;
        this.calendarEl = document.getElementById('calendar');
        this.events = [];
        this._isLoading = false;
        this.calendar_start = null;
        this.calendar = new FullCalendar.Calendar(this.calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'nl',
            firstDay: 1,
            validRange: {
                start: moment(new Date().addDays(1)).format('YYYY-MM-DD')
            },
            events: (i, s, f) => this.getEvents(i, s, f),
            datesSet: (data) => this.onMonthChange(data),
            dateClick: (data) => this.onDateClick(data),
            eventWillUnmount: (info) => this.eventRender(info),
            eventDidMount: (info) => this.eventPositioned(info)
        });
        this.property = null;
        this._loadFromStorage();
        await this.updateCalendarAvailability();

        setTimeout(() => {
            window.dispatchEvent(new Event('resize'), 100);
        }, 500);
        
    }

    reset() {
        localStorage.removeItem(`booking-form-${this.propertyId}`);
        this.init(this.propertyId);
    }

    _loadFromStorage() {
        let data = loadLocalStorage(this.propertyId);
        if (data == null) return;
        this.bookingEvent = data.bookingData;
    }

    subscribe(cls) {
        this.listeners.push(cls);
    }

    setLoading(newValue) {
        this.onLoadingChange(this._isLoading, newValue);
        this._isLoading = newValue;
    }

    onLoadingChange(prev, newVal) {
        if (newVal) {
            document.getElementById('loadingCalendar').classList.remove('d-none');
            document.getElementById('calendar').classList.add('opacity-50');
        } else {
            document.getElementById('loadingCalendar').classList.add('d-none');
            document.getElementById('calendar').classList.remove('opacity-50');
        }
    }

    eventRender({event, el, isMirror, isStart, isEnd, view}) {
        // if (event._def.extendedProps.available == 'end') {
        //     el.style.background = "linear-gradient(45deg, rgba(255,0,0,1) 0%, rgba(255,0,0,1) 50%, rgba(0,255,0,1) 50%, rgba(0,255,0,1) 100%);"
        // } else if (event._def.extendedProps.available == 'start') {
        //     el.style.background = "linear-gradient(225deg, rgba(255,0,0,1) 0%, rgba(255,0,0,1) 50%, rgba(0,255,0,1) 50%, rgba(0,255,0,1) 100%);"
        // }
        // el.style.background = 'black';
        // console.log(el);
    }

    eventPositioned({event, el, isMirror, isStart, isEnd, view}) {
        if(el instanceof HTMLAnchorElement) return;

        let date = event._def.extendedProps.available;

        if (date.arrival == false && date.all_day == false && date.departure == true) {
            el.classList.add('event-end')
        } else if (date.arrival == true && date.all_day == false && date.departure == false) {
            el.classList.add('event-start');
        }
        if (date.in_option == true) {
            el.classList.add('in_option');
        }
    }

    renderCalendar() {
        this.calendar.refetchEvents();
        this.calendar.render();
    }

    getEvents(info, successCallback, failureCallBack) {
        successCallback(this.events);
    }

    async onDateClick(data) {
        if (!this.canInteract) return;
        let reversed = false;
        let availabilityEvent = this.events.find((e) => e.start == moment(data.date).format('YYYY-MM-DD'));

        if (availabilityEvent == null) return;

        // if not available - red only = disallowed, red/green = allowed
        if (
            availabilityEvent.available.all_day == false  &&
            availabilityEvent.available.arrival == false  &&
            availabilityEvent.available.departure == false 
        ) {
            return;
        }
        
        // gray is not allowed to select, 
        if (
            availabilityEvent.available.all_day == true && 
            availabilityEvent.available.arrival == false  &&
            availabilityEvent.available.departure == false 
        ) {
            return;
        }

        // reset bookingevent
        if (this.bookingEvent != null && this.bookingEvent.start != this.bookingEvent.end) {
            this.events.splice(this.events.indexOf(this.bookingEvent, 1));
            this.bookingEvent = null;
        }

        // create new bookingevent
        if (this.bookingEvent == null) {
            this.bookingEvent = {
                start: data.date,
                end: data.date,
                allDay: true,
                editable: false,
            };
            this.events.push(this.bookingEvent);
        } else if (data.date < this.bookingEvent.start) {
            // allow reverse selection
            this.bookingEvent.end = this.bookingEvent.start.addDays(1);
            this.bookingEvent.start = data.date;
            reversed = true;
        } else {
            // update end date
            this.bookingEvent.end = data.date.addDays(1);
        }

        // validate if it is possible, reset if not
        if (this.bookingEvent != null && this.bookingEvent.start != this.bookingEvent.end) {
            var currDate = moment(this.bookingEvent.start).add(1,'days').startOf('day');
            var isAvailable = true;
            let error = 'The selection is not available';

            let amountOfNights = moment.duration(moment(this.bookingEvent.end).diff(currDate), 'milliseconds').asDays();
            const minNights = this._isAvailableOnDate(currDate).minimum_nights;
            if (minNights > 0 && amountOfNights < minNights) {
                isAvailable = false;
                error = `Minimum nights not met: ${minNights} nights`
            }

            while (currDate.diff(moment(this.bookingEvent.end).add(-1, 'days').startOf('day')) < 0) {
                let availabilityData = this._isAvailableOnDate(currDate);

                isAvailable = isAvailable && availabilityData.all_day == true;
                if (!isAvailable) {
                    error = `Date(s) in between are not available`;
                }

                currDate.add(1, 'days')
            }

            if (!isAvailable) {
                if (reversed) {
                    this.bookingEvent.start = this.bookingEvent.end;
                } else {
                    this.bookingEvent.end = this.bookingEvent.start;
                }
                Swal.fire({
                    title: 'Woops...',
                    text: error,
                    icon: 'error',
                    confirmButtonText: 'Oke!'
                });
                return;
            };
        }

        // set title
        if (this.bookingEvent != null) {
            let end = this.bookingEvent.end != this.bookingEvent.start ? this.bookingEvent.end.addDays(-1) : null;
            this.bookingEvent.title = `Van ${this.formatDate(this.bookingEvent.start) || '...'} t/m ${this.formatDate(end) || '...'}`;
        }

        if (this.bookingEvent != null && this.bookingEvent.start != this.bookingEvent.end) {
            this.notify('onValidBookingDate', { property: this.property, bookingEvent: this.bookingEvent });
        }

        this.calendar.refetchEvents();
        this.notify('onDateClick', data);
    }

    _isAvailableOnDate(date) {
        return this.property.availability[date.format('YYYY-MM-DD')];
    }

    notify(type, data) {
        this.listeners.forEach(lis => {
            if (type == 'onDateClick') {
                lis.onDateClick(data);
            }
            if (type == 'onValidBookingDate') {
                lis.onValidBookingDate(data);
            }
            if (type == 'onPropertyLoaded') {
                lis.onPropertyLoaded(data);
            }
        });
    }

    formatDate(date) {
        if (date == null) return date;
        return moment(date).format('DD-MM-YYYY');
    }

    async onMonthChange(data) {
        let bookingEvent = this.bookingEvent;
        const cs = moment(this.calendar_start).format('D-M-Y');
        const ds = moment(data.start).format('D-M-Y');

        if (cs != ds) {
            await this.updateCalendarAvailability(data.start, data.end);
            this.calendar_start = data.start;

            if (bookingEvent != null) {
                this.bookingEvent = bookingEvent;
                this.events.push(this.bookingEvent);
                this.calendar.refetchEvents();
            }
        }
    }

    updateCalendarAvailability(start_date = null, end_date = null) {
        return new Promise(async (resolve, reject) => { 
            this.getPropertyById(this.propertyId, start_date, end_date)
                .then((data) => {
                    let events = [];
                    for (let key in data.availability) {
                        let date = data.availability[key];
                        
                        // orange = start or end
                        let bgcolor = 'white';

                        if (date.all_day == true && date.arrival == false && date.departure == false) {
                            bgcolor = 'gray'; 
                        } else if(date.all_day === true) {
                            bgcolor = 'rgba(40,167,69,1)';
                        } else if (date.all_day === false && date.in_option == true) 
                        {
                            bgcolor = 'rgba(220,210,53,1)';
                        } else if (date.all_day === false) {
                            bgcolor = 'rgba(220,53,69,1)';
                        }

                        events.push({
                            start: key,
                            end: key,
                            display: 'background',
                            backgroundColor: bgcolor,
                            allDay: true,
                            available: date,
                        });
                    }
                    this.events = events;
                }).catch((err) => {
                    // allow catch
                    rejecy();
                }).finally(() => {
                    this.renderCalendar();
                    resolve();
                });
        });
    }

    async getPropertyById(propertyId, start_date, end_date) {
        let endpoint = `/properties/${propertyId}`;
        const start = (start_date || new Date()).addDays(1).toISOString().split('T')[0];
        const end = (end_date || (new Date()).addDays(30)).addDays(30).toISOString().split('T')[0];

        this.calendar_start = start;

        let query = {
            'start_date': start,
            'end_date': end,
        };
        this.setLoading(true);
        return new Promise((resolve, reject) => {
            axios.get(`${BASE_URL}${endpoint}`, { params: query })
                .then((res) => {
                    this.property = res.data.data;
                    this.notify('onPropertyLoaded', res.data.data);
                    resolve(res.data.data);
                })
                .catch((err) => {
                    reject(err);
                })
                .finally(() => {
                    this.setLoading(false);
                })
        });
    }
}



//======================================================================
// BookingForm (combined with BookingCalendar)
//======================================================================
class BookingForm {

    constructor(propId, calendar) {
        this.init(propId, calendar);
    }

    init(propId, calendar) {
        this.calendar = calendar;
        this.propertyId = propId;
        this.el = document.getElementById('booking_form');
        this.el.addEventListener('submit', this.onFormSubmit.bind(this));

        this.inputs = {
            arrival_date: new InputField('#arrival_date', { type: 'date' }),
            departure_date: new InputField('#departure_date', { type: 'date' }),
            amount_of_tenants: new InputField('#amount_of_tenants'),
            rental_price: new InputField('#rental_price', { type: 'price' }),
            reservation_costs: new InputField('#reservation_costs', { type: 'price' }),
            total_price: new InputField('#total_price', { type: 'price' }),
            additional_options: [],
        };
        this.inputs.amount_of_tenants.addEventListener('change', this.onTenantsChange.bind(this));

        this._reset(false);
        this.bookingData = null;
        this.property = null;
        this.stepper = null;
        this.submitted = false;
    }

    onTenantsChange(event) {
        this.inputs.amount_of_tenants.value = event.target.value;
        if (this.bookingData != null) {
            this._updateRentalPrice(this.bookingData);
            this._saveToStorage();
        }
    }

    _reset(saveToStorage = true) {
        for (let k in this.inputs) {
            if (k == 'amount_of_tenants') continue;
            if (k == 'additional_options') continue;
            this.inputs[k].setValue('');
        }
        if (saveToStorage) this._saveToStorage();
    }

    reset() {
        localStorage.removeItem(`personal-data-${this.propertyId}`);

        this.el.removeEventListener('submit', this.onFormSubmit.bind(this));

        this.init(this.propertyId, this.calendar);
    }

    onDateClick(data) {
        if (this.bookingData != null) {
            this.bookingData = null;
            this._reset();
        }
    }

    onValidBookingDate({ property, bookingEvent }) {
        setTimeout(() => {
            this.bookingData = bookingEvent;
            this.property = property;
            this._updateArrivalDate(bookingEvent.start);
            this._updateDepartureDate(bookingEvent.end);
            this._updateRentalPrice(bookingEvent);
            this._updateAdditionalOptions(bookingEvent);
            this._updateReservationCosts(bookingEvent);
            this._updateTotalPrice();
            this._saveToStorage();
            document.querySelector('[data-connect="start-booking-btn"]').classList.remove('d-none');
        }, 50);
    }

    onPropertyLoaded(property) {
        this._updateAmountOfTenants(property.max_persons);
        this._loadFromStorage();
        this._updateAmountOfTenants(property.max_persons);
        this.property = property;

        if (this.bookingData != null) {
            this._updateRentalPrice(this.bookingData);
            this._updateReservationCosts(this.bookingData);
            this._updateAdditionalOptions(this.bookingData);
            this._updateTotalPrice();
            // add additional options to inputs
        }
    }

    toJSON() {
        return {
            arrival_date: this.inputs.arrival_date.el.value,
            departure_date: this.inputs.departure_date.el.value,
            amount_of_tenants: this.inputs.amount_of_tenants.el.value,
            rental_price: this.inputs.rental_price.getValue(),
            reservation_costs: this.inputs.reservation_costs.getValue(),
            total_price: this.inputs.total_price.getValue(),
            bookingData: this.bookingData,
            property: this.property,
            updated_at: Date.now(),
            submitted: this.submitted,
        };
    }

    onFormSubmit(event) {
        event.preventDefault();
        if (this.inputs.amount_of_tenants.value < 1) {
            Swal.fire({
                text: "Aantal personen kan niet 0 zijn",
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        } else {
            this.submitted = true;
            this._saveToStorage();
            this.stepper = new Stepper(this);
        }
    }

    _updateAdditionalOptions(bookingData) {
        const matchingPrice = this._getMatchingPriceForDate(bookingData.start);

        const groupEl = document.getElementById('additional_options_group');
        if (matchingPrice == null) {
            groupEl.innerHTML = "";
            return;
        }

        const priceOptions = matchingPrice.meta.additional_options;

        // reset additional_options
        if (this.bookingData.additional_options == undefined || this.bookingData.additionalOptions == null) {
            this.bookingData.additional_options = [];
        }

        if (priceOptions.length == 0) {
            groupEl.innerHTML = "";
            return;
        }

        groupEl.innerHTML = "";
        priceOptions.forEach((opt) => {
            const id = `${opt.name}${opt.price}${opt.interval}`;
            let addOptObj = { ...opt, id: id, added: false };

            const existingObj = this.bookingData.additional_options.find(opt => opt.id == id);
            if (existingObj == null) {
                this.bookingData.additional_options.push(addOptObj);
            } else {
                addOptObj = existingObj;
            }

            // create div
            // create label
            // create input of type checkbox
            // add event listener to checkbox

            let wrap = document.createElement('div');
            wrap.classList.add('form-check');

            let lbl = document.createElement('label');
            const interv = opt.interval == 'total' ? 'totaal extra' : 'per week extra';
            lbl.classList.add('form-check-label');
            lbl.setAttribute('for', id);
            lbl.innerText = `+ ${opt.name} (€${opt.price} ${interv}) `;

            let input = document.createElement('input');
            input.classList.add('form-check-input');
            input.type = 'checkbox';
            input.id = id;
            input.checked = addOptObj.added;
            input.addEventListener('change', () => {
                if (this.bookingData != null) {
                    if (input.checked) {
                        addOptObj.added = true;
                    } else {
                        addOptObj.added = false;
                    }
                    this._updateRentalPrice(this.bookingData);
                    this._saveToStorage();
                }
            });

            wrap.append(input, lbl);
            groupEl.append(wrap);
        });

        this.inputs.additional_options = this.bookingData.additional_options;
    }

    _saveToStorage() {
        let data = this.toJSON();
        localStorage.setItem(`booking-form-${this.property.id}`, JSON.stringify(data));
    }

    _loadFromStorage() {
        let data = loadLocalStorage(this.propertyId);
        if (data == null) return;

        this.inputs.arrival_date.setValue(moment(data.arrival_date, 'DD-MM-YYYY').startOf('day'));
        this.inputs.departure_date.setValue(moment(data.departure_date, 'DD-MM-YYYY').startOf('day'));
        this.inputs.rental_price.setValue(data.rental_price);
        this.inputs.reservation_costs.setValue(data.reservation_costs);
        this.inputs.total_price.setValue(data.total_price);
        this.bookingData = data.bookingData;
        this.property = data.property;
        this.submitted = data.submitted;

        this._updateAmountOfTenants(data.property.max_persons);
        this.inputs.amount_of_tenants.el.value = data.amount_of_tenants;

        if (data.arrival_date != null && data.departure_date != null) {
            document.querySelector('[data-connect="start-booking-btn"]').classList.remove('d-none');
        }

        if (data.submitted && this.stepper == null) {
            this.stepper = new Stepper(this);
        }
    }

    _updateAmountOfTenants(amount) {
        let currentVal = this.inputs.amount_of_tenants.el.value || 0;

        this.inputs.amount_of_tenants.el.innerHTML = '';
        for (let i = 0; i <= amount; i++) {
            let opt = document.createElement('option');
            opt.value = i;
            opt.text = i;
            this.inputs.amount_of_tenants.el.append(opt);
        }
        this.inputs.amount_of_tenants.el.value = currentVal;
    }

    _updateArrivalDate(date) {
        this.inputs.arrival_date.setValue(date);
    }

    _updateDepartureDate(date) {
        this.inputs.departure_date.setValue(date.addDays(-1));
    }

    _getMatchingPriceForDate(date) {
        return this.property.prices.sort((a,b) => a.price - b.price).find((pObj) => {
            return moment(date).isBetween(moment(pObj.start_date, 'YYYY-MM-DD'), moment(pObj.end_date, 'YYYY-MM-DD'), 'days', '[]');
        });
    }

    _updateRentalPrice(bookingData) {
        // Search for matching price
        bookingData.start = moment(bookingData.start, 'YYYY-MM-DD').format('YYYY-MM-DD');
        bookingData.end = moment(bookingData.end, 'YYYY-MM-DD').format('YYYY-MM-DD');
        
        let date = moment(bookingData.start, 'YYYY-MM-DD').startOf('day');
        let end_date = moment(bookingData.end, 'YYYY-MM-DD').add(-1, 'days');
        let price = 0;

        while(end_date.diff(date, 'days') > 0) {
            
            let matchingPrice = this._getMatchingPriceForDate(date);
            if (matchingPrice == null) { this.inputs.rental_price.value = "Not available"; return; }

            // Calculate price
            let weekPrice = matchingPrice.is_sale ? matchingPrice.sale_price : matchingPrice.price;
            let tenants = this.inputs.amount_of_tenants.el.value;

            if (matchingPrice.meta.additional_pricing_option == 'different_price_per_persons_range') {
                let matchingPricePerPersonsRange = matchingPrice.meta.additional_pricing_data.price_per_persons_range.find((obj) => {
                    return tenants >= obj.min && tenants <= obj.max;
                });

                if (matchingPricePerPersonsRange != null) weekPrice = parseFloat(matchingPricePerPersonsRange.price);
            } else if (matchingPrice.meta.additional_pricing_option == 'allowance_person_week') {    
                if(tenants > matchingPrice.meta.additional_pricing_data.start_from) {
                    let ppp = parseFloat(matchingPrice.meta.additional_pricing_data.price);
                    weekPrice = parseFloat(weekPrice) + parseFloat((ppp * (tenants - matchingPrice.meta.additional_pricing_data.start_from)));
                }
            }

            price += weekPrice / 7;
            date.add(1, 'days');
        }
        
        this.inputs.rental_price.setValue(price);

        let amountOfDays = moment(bookingData.end).diff(moment(bookingData.start), 'days') - 1;

        // additional_options
        this.bookingData.additional_options_total = 0;
        if (bookingData.additional_options != undefined) {
            bookingData.additional_options.filter(opt => opt.added).forEach((opt) => {
                if (opt.interval == 'total') {
                    this.bookingData.additional_options_total += parseFloat(opt.price);
                } else {
                    this.bookingData.additional_options_total += parseFloat(opt.price) / 7 * amountOfDays;
                }
            });
        }
        
        this._updateTotalPrice();
    }

    _updateReservationCosts(data) {
        this.inputs.reservation_costs.setValue(this.property.reservation_costs);
        this._updateTotalPrice();
    }

    _updateTotalPrice() {
        let total = parseFloat(this.inputs.rental_price.getValue()) + parseFloat(this.inputs.reservation_costs.getValue());

        total += this.bookingData.additional_options_total;

        this.inputs.total_price.setValue(total);
    }
}



//======================================================================
// Other Classes
//======================================================================
class InputField {

    onChange = (prevVal, newVal) => { };

    constructor(el, { type, autoUpdate } = { type: 'text', autoUpdate: false }) {
        this.value = null;
        this.type = type;

        // allow pass in of element or selector by string
        if (el instanceof String || typeof el === 'string') {
            el = document.querySelector(el);
        }
        this.el = el;

        this.listeners = [];

        if (autoUpdate) {
            let actions = ['change', 'keyup', 'paste', 'select', 'mouseup'];
            actions.forEach((act) => {
                this.addEventListener(act, (e) => {
                    this.value = e.target.value;
                    this.onChange(this.value, e.target.value);
                });
            });
            
            this.onChange(this.value, this.el.value);
        }
    }

    addEventListener(action, callb) {
        this.el.addEventListener(action, callb);
    }

    setValue(val) {
        if (this.type == 'price') {
            this.value = parseFloat(val).toFixed(2);
        } else {
            this.value = val;
        }
        this._updateElement(val);
    }

    getValue() {
        if (this.el.value != '' && this.value == null) {
            this.value = this.el.value;
        }
        return this.value;
    }

    _updateElement(val) {
        if (val != '' && val != null) {
            if (this.type == 'price') {
                val = this._parsePrice(val);
            }
            if (this.type == 'date') {
                val = moment(val).format('DD-MM-YYYY');
            }
            if (this.type == 'select') {
                this.value = this.el.value;
                this.onChange(this.value, this.value);
            }
        }
        this.el.value = val;
    }

    _parsePrice(price) {
        let p = parseFloat(`${price}`).toFixed(2);
        return `${p}`.replace('.', ',');
    }

    getValueParsed() {
        let val = this.value;
        if (this.type == 'price') {
            val = this._parsePrice(val);
        }
        if (this.type == 'date') {
            val = moment(val).format('DD-MM-YYYY');
        }
        return val;
    }
}



//======================================================================
// Stepper (wizard)
//======================================================================
class Stepper {
    constructor(bookingForm) {
        this.bookingForm = bookingForm;
        this.propertyId = bookingForm.propertyId;
        document.querySelector('#booking-stepper').classList.remove('d-none');
        document.querySelector('[data-connect="start-booking-btn"]').classList.add('d-none');

        let btnBookingformStart = document.getElementById('booking-stepper');
        btnBookingformStart.scrollIntoView();

        var triggerTabList = [].slice.call(document.querySelectorAll('#booking-tab button'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });

        document.querySelectorAll('button[data-bs-target="#booking-personal-data"]').forEach((el) => {
            el.addEventListener('click', () => {
                this.switchToStep('stepOne');
            });
        });
        document.querySelectorAll('button[data-bs-target="#booking-check-personal-data"]').forEach((el) => {
            el.addEventListener('click', () => {
                this.switchToStep('stepTwo');                
            });
        });
        document.querySelectorAll('button[data-bs-target="#booking-contact"]').forEach((el) => {
            el.addEventListener('click', () => {
                this.switchToStep('stepThree');
            });
        });

        document.querySelectorAll('button[data-target="step1"]').forEach(btn => {
            btn.addEventListener('click', () => {
                var triggerEl = document.querySelector('#booking-tab button[data-bs-target="#booking-personal-data"]');
                if(bootstrap.Tab.getInstance !== undefined) {
                    bootstrap.Tab.getInstance(triggerEl).show();
                } else {
                    jQuery(triggerEl).tab('show');
                }
                this.switchToStep('stepOne');
            });
        });

        document.querySelectorAll('button[data-target="step2"]').forEach(btn => {
            btn.addEventListener('click', () => {
                // Validate the form on step 1 ? if im on step 1...
                let valid = true;
                if (btn.hasAttribute('data-validate')) {
                    const form = document.querySelector(btn.getAttribute('data-validate'));
                    valid = form.checkValidity();

                    if (!valid) {
                        form.scrollIntoView(
                            {
                                behavior: "smooth",
                                block: "end",
                                inline: "nearest"
                            }
                        );
                    }

                    form.classList.add('was-validated');
                }

                if (valid) {
                    var triggerEl = document.querySelector('#booking-tab button[data-bs-target="#booking-check-personal-data"]');
                    if(bootstrap.Tab.getInstance !== undefined) {
                        bootstrap.Tab.getInstance(triggerEl).show();
                    } else {
                        jQuery(triggerEl).tab('show');
                    }
                    this.switchToStep('stepTwo');
                    let btnBookingformStart = document.getElementById('booking-stepper');
                    btnBookingformStart.scrollIntoView();
                }

            });
        });

        document.querySelectorAll('button[data-target="step3"]').forEach(btn => {
            btn.addEventListener('click', () => {
                var triggerEl = document.querySelector('#booking-tab button[data-bs-target="#booking-confirmation"]');
                if(bootstrap.Tab.getInstance !== undefined) {
                    bootstrap.Tab.getInstance(triggerEl).show();
                } else {
                    jQuery(triggerEl).tab('show');
                }
                this.switchToStep('stepThree');
                let btnBookingformStart = document.getElementById('booking-stepper');
                btnBookingformStart.scrollIntoView();
            });
        });

        this.steps = {
            stepOne: new StepOne(this),
            stepTwo: new StepTwo(this),
            stepThree: new StepThree(this),
        };
        this.current_step = this.steps.stepOne;
    }

    switchToStep(stepName) {
        switch (stepName) {
            case 'stepOne':
                this.current_step = this.steps.stepOne;
                break;
            case 'stepTwo':
                this.current_step = this.steps.stepTwo;
                break;
            case 'stepThree':
                this.current_step = this.steps.stepThree;
                break;
        }

        this.current_step.onSwitch();
    }
}


class StepOne {
    constructor(stepper) {
        this.stepper = stepper;
        this.propertyId = stepper.propertyId;

        this.formPersonInfo = {
            gender: new InputField('#main-tenant-data select[name="gender"]', { type: 'select', autoUpdate: true }),
            firstname: new InputField('#main-tenant-data input[name="firstname"]', { autoUpdate: true }),
            lastname: new InputField('#main-tenant-data input[name="lastname"]', { autoUpdate: true }),
            street: new InputField('#main-tenant-data input[name="street"]', { autoUpdate: true }),
            housNr: new InputField('#main-tenant-data input[name="housenumber"]', { autoUpdate: true }),
            zipcode: new InputField('#main-tenant-data input[name="zipcode"]', { autoUpdate: true }),
            city: new InputField('#main-tenant-data input[name="city"]', { autoUpdate: true }),
            country: new InputField('#main-tenant-data select[name="country"]', { type: 'select', autoUpdate: true }),
            phoneNumber: new InputField('#main-tenant-data input[name="phonenumber"]', { autoUpdate: true }),
            phoneSecondary: new InputField('#main-tenant-data input[name="secondary-phonenumber"]', { autoUpdate: true }),
            dateOfBirth: new InputField('#main-tenant-data input[name="date_of_birth"]', { autoUpdate: true }),
            email: new InputField('#main-tenant-data input[name="email"]', { autoUpdate: true }),
            emailConfirmation: new InputField('#main-tenant-data input[name="email-check"]', { autoUpdate: true }),
            comment: new InputField('#main-tenant-data textarea[name="comment"]', { autoUpdate: true }),
            language: new InputField('#main-tenant-data select[name="country"]', { type: 'select', autoUpdate: true }),
        };

        this.subTenantForms = [];

        this._loadFromStorage();

        for (let key in this.formPersonInfo) {
            this.formPersonInfo[key].onChange = (prevVal, newVal) => { this._saveToStorage(); };
        }

        this.formPersonInfo.gender.value = 'm';
        this.formPersonInfo.country.value = 'NLD';

        this._saveToStorage();

        this._matchSubTenantsWithAmount(this.stepper.bookingForm.toJSON().amount_of_tenants);

        this.stepper.bookingForm.inputs.amount_of_tenants.addEventListener('change', (data) => {
            this._matchSubTenantsWithAmount(this.stepper.bookingForm.toJSON().amount_of_tenants);
        });
    }

    async _loadFromStorage() {
        let data = localStorage.getItem(`personal-data-${this.propertyId}`);
        if (data == null) return;
        data = JSON.parse(data);

        this.formPersonInfo.gender.setValue(data.mainTenant.gender);
        this.formPersonInfo.firstname.setValue(data.mainTenant.firstname);
        this.formPersonInfo.lastname.setValue(data.mainTenant.lastname);
        this.formPersonInfo.street.setValue(data.mainTenant.street);
        this.formPersonInfo.housNr.setValue(data.mainTenant.housNr);
        this.formPersonInfo.zipcode.setValue(data.mainTenant.zipcode);
        this.formPersonInfo.city.setValue(data.mainTenant.city);
        this.formPersonInfo.country.setValue(data.mainTenant.country);
        this.formPersonInfo.phoneNumber.setValue(data.mainTenant.phoneNumber);
        this.formPersonInfo.phoneSecondary.setValue(data.mainTenant.phoneSecondary);
        this.formPersonInfo.dateOfBirth.setValue(data.mainTenant.dateOfBirth);
        this.formPersonInfo.email.setValue(data.mainTenant.email);
        this.formPersonInfo.emailConfirmation.setValue(data.mainTenant.emailConfirmation);
        this.formPersonInfo.comment.setValue(data.mainTenant.comment);

        this._matchSubTenantsWithAmount(this.stepper.bookingForm.inputs.amount_of_tenants.getValue());

        data.subTenants.forEach((obj, index) => {
            this.subTenantForms[index].gender.setValue(obj.gender); this.subTenantForms[index].gender.el.value = obj.gender;
            this.subTenantForms[index].firstname.setValue(obj.firstname);
            this.subTenantForms[index].lastname.setValue(obj.lastname);
            this.subTenantForms[index].dateOfBirth.setValue(obj.dateOfBirth);
        });
    }

    _saveToStorage() {
        let data = this.toJSON();
        localStorage.setItem(`personal-data-${this.propertyId}`, JSON.stringify(data));
    }

    toJSON() {
        let data = {
            mainTenant: {},
            subTenants: [],
        };
        for (let key in this.formPersonInfo) {
            data.mainTenant[key] = this.formPersonInfo[key].getValue();
        }
        for (let row in this.subTenantForms) {
            let dataRow = {};
            for (let key in this.subTenantForms[row]) {
                dataRow[key] = this.subTenantForms[row][key].getValue();
            }
            data.subTenants.push(dataRow);
        }
        return data;
    }

    _matchSubTenantsWithAmount(amount) {
        let el = document.querySelector('div[data-copy="booking-subtenant-row"]');

        if (amount == 1) {
            el.parentNode.parentNode.parentNode.classList.add('d-none');
            return;
        } else {
            el.parentNode.parentNode.parentNode.classList.remove('d-none');
        }

        while (el.parentNode.children.length < amount - 1) {
            let copy = el.cloneNode(true);
            let inputs = copy.querySelectorAll('input');
            inputs.forEach((i) => {
                i.value = '';
            });
            el.parentNode.appendChild(copy);
        }

        while (el.parentNode.children.length > amount - 1) {
            el.parentElement.removeChild(el.parentNode.children[el.parentNode.children.length - 1]);
        }

        // foreach row, add to class as data
        this.subTenantForms = [];
        for (let row in el.parentElement.children) {
            let rowHTML = el.parentElement.children[row];
            if ((rowHTML instanceof HTMLElement) == false) continue;
            let dataRow = {
                gender: new InputField(rowHTML.querySelector('select[name="gender"]'), { type: 'select', autoUpdate: true }),
                firstname: new InputField(rowHTML.querySelector('input[name="firstname"]'), { type: 'text', autoUpdate: true }),
                lastname: new InputField(rowHTML.querySelector('input[name="lastname"]'), { type: 'text', autoUpdate: true }),
                dateOfBirth: new InputField(rowHTML.querySelector('input[name="date_of_birth"]'), { type: 'text', autoUpdate: true })
            };

            for (let key in dataRow) {
                dataRow[key].onChange = (prevVal, newVal) => { this._saveToStorage(); };
            }

            this.subTenantForms.push(dataRow);
        }
    }

    onSwitch() {
        document.getElementById('amount_of_tenants').disabled = false;
        document.querySelectorAll('#additional_options_group input').forEach((input) => {
            input.disabled = false;
        });
        this.stepper.bookingForm.calendar.canInteract = true;
    }
}



class StepTwo {
    constructor(stepper) {
        this.stepper = stepper;

        this.fields = {
            mainTenant: {
                name: document.querySelector('#booking-check-personal-data [data-connect="name-check"]'),
                address: document.querySelector('#booking-check-personal-data [data-connect="address-check"]'),
                zipcode_city: document.querySelector('#booking-check-personal-data [data-connect="zipcode-city-check"]'),
                country: document.querySelector('#booking-check-personal-data [data-connect="country-check"]'),
                phone: document.querySelector('#booking-check-personal-data [data-connect="phone-check"]'),
                phoneSecondary: document.querySelector('#booking-check-personal-data [data-connect="secondary-phone-check"]'),
                email: document.querySelector('#booking-check-personal-data [data-connect="email-check"]'),
                dateOfBirth: document.querySelector('#booking-check-personal-data [data-connect="date-of-birth-check"]'),
                comment: document.querySelector('#booking-check-personal-data [data-connect="comment-check"]')
            },
            subTenantsWrapper: document.querySelector('#booking-check-personal-data [data-connect="subtenants-check"]'),
        };
    }

    onSwitch() {
        document.getElementById('amount_of_tenants').disabled = true;
        document.querySelectorAll('#additional_options_group input').forEach((input) => {
            input.disabled = true;
        });
        this.stepper.bookingForm.calendar.canInteract = false;

        let personInfo = this.stepper.steps.stepOne.formPersonInfo;
        this.fields.mainTenant.name.innerHTML = `${personInfo.gender.getValue() == 'm' ? 'Dhr.' : 'Mevr.'} ${personInfo.firstname.getValue()} ${personInfo.lastname.getValue()}`;
        this.fields.mainTenant.address.innerHTML = `${personInfo.street.getValue()} ${personInfo.housNr.getValue()}`;
        this.fields.mainTenant.zipcode_city.innerHTML = `${personInfo.zipcode.getValue()} ${personInfo.city.getValue()}`;
        this.fields.mainTenant.country.innerHTML = `${personInfo.country.getValue()}`;
        this.fields.mainTenant.phone.innerHTML = `${personInfo.phoneNumber.getValue()}`;
        this.fields.mainTenant.phoneSecondary.innerHTML = `${personInfo.phoneSecondary.getValue()}`;
        this.fields.mainTenant.email.innerHTML = `${personInfo.email.getValue()}`;
        this.fields.mainTenant.dateOfBirth.innerHTML = `${moment(personInfo.dateOfBirth.getValue()).format('DD-MM-YYYY')}`;
        this.fields.mainTenant.comment.innerHTML = `${personInfo.comment.getValue() || ''}`;

        let subtenants = this.stepper.steps.stepOne.subTenantForms;
        let rowCopy = this.fields.subTenantsWrapper.children[0].cloneNode(true);
        this.fields.subTenantsWrapper.innerHTML = '';

        if (this.stepper.bookingForm.inputs.amount_of_tenants.getValue() == 0 || subtenants.length == 0) {
            this.fields.subTenantsWrapper.parentNode.classList.add('d-none');
        }
        else {
            this.fields.subTenantsWrapper.parentNode.classList.remove('d-none');
        }

        subtenants.forEach((st) => {
            let newRow = rowCopy.cloneNode(true);
            newRow.querySelector('p').innerHTML = `${st.gender.getValue() == 'm' ? 'Dhr.' : 'Mevr.'} ${st.firstname.getValue()} ${st.lastname.getValue()} (${moment(st.dateOfBirth.getValue()).format('DD-MM-YYYY')})`;
            this.fields.subTenantsWrapper.appendChild(newRow);
        });

    }
}



class StepThree {
    constructor(stepper) {
        this.stepper = stepper;
        this.fields = {
            arrivalDate: document.querySelector('#booking-confirmation [data-connect="arrival-check"]'),
            departureDate: document.querySelector('#booking-confirmation [data-connect="departure-check"]'),
            amountOfPersons: document.querySelector('#booking-confirmation [data-connect="amount-of-persons-check"]'),
            totalPrice: document.querySelector('#booking-confirmation [data-connect="total-price-check"]'),
            additional_options: document.querySelector('#booking-confirmation [data-connect="additional-options-check"]')
        };

        document.getElementById('confirm-booking-form').addEventListener('submit', (e) => {
            this.onSubmitForm(e);
        });
    }

    onSwitch() {
        document.getElementById('amount_of_tenants').disabled = true;
        document.querySelectorAll('#additional_options_group input').forEach((input) => {
            input.disabled = true;
        });

        this.stepper.bookingForm.calendar.canInteract = false;

        this.fields.arrivalDate.innerHTML = this.stepper.bookingForm.inputs.arrival_date.getValueParsed();
        this.fields.departureDate.innerHTML = this.stepper.bookingForm.inputs.departure_date.getValueParsed();
        this.fields.amountOfPersons.innerHTML = this.stepper.bookingForm.inputs.amount_of_tenants.getValueParsed();
        this.fields.totalPrice.innerHTML = `&euro; ${this.stepper.bookingForm.inputs.total_price.getValueParsed()}`;

        // additional options
        this.fields.additional_options.innerHTML = '';
        this.stepper.bookingForm.inputs.additional_options.filter(opt => opt.added).forEach((opt) => {
            this.fields.additional_options.innerHTML += ` + ${opt.name} <br>`;
        });
    }

    onSubmitForm(e) {
        e.preventDefault();

        let valid = e.target.checkValidity();

        e.target.classList.add('was-validated');

        if (!valid) {
            return;
        }

        const hiddeninputs = {
            successMessage: document.getElementById('on-booking-success-message').value,
            failMessage: document.getElementById('on-booking-fail-message').value,
            successPageId: document.getElementById('success-page-id').value,
        }

        this.postBooking().then(() => {
            localStorage.removeItem(`booking-form-${this.stepper.propertyId}`);
            localStorage.removeItem(`personal-data-${this.stepper.propertyId}`);
            Swal.fire({
                // text: 'Bedankt voor uw boeking, u ontvangt een zo spoeidig mogelijk een bevestigingsmail.',
                html: hiddeninputs.successMessage,
                icon: 'success',
                confirmButtonText: 'Ok'
            }).then((isConfirm) => {
                if (isConfirm) {
                    window.location.href = `/index.php?page_id=${hiddeninputs.successPageId}`;
                }
            });
        }).catch((err) => {
            console.debug(err);
            Swal.fire({
                html: hiddeninputs.failMessage,
                icon: 'error',
                confirmButtonText: 'Ok'
            });
        });

    }

    postBooking() {
        return new Promise((resolve, reject) => {
            let bookingForm = this.stepper.bookingForm;
            let additionalOptions = bookingForm.inputs.additional_options.filter(opt => opt.added);
            let tenantInfo = this.stepper.steps.stepOne.formPersonInfo;
            let subTenantsInfo = this.stepper.steps.stepOne.subTenantForms;

            let addr = `${tenantInfo.street.getValue()} ${tenantInfo.housNr.getValue()}, ${tenantInfo.zipcode.getValue()} ${tenantInfo.city.getValue()}, ${tenantInfo.country.getValue()}`;

            let tenantsLang = 'en';

            const is_option_el = document.getElementById('is_option_checkbox');
            const is_option = is_option_el != undefined ? is_option_el.checked == true : false;

            if(tenantInfo.country.getValue() == 'NLD') {
                tenantsLang = 'nl';
            } else {
                tenantsLang = 'en';
            }

            let body = {
                from_date: moment(bookingForm.inputs.arrival_date.getValue()).format('YYYY-MM-DD'),
                to_date: moment(bookingForm.inputs.departure_date.getValue()).format('YYYY-MM-DD'),
                is_option: is_option,
                price: bookingForm.inputs.total_price.getValue(),
                additional_options: additionalOptions,
                tenant: {
                    gender: tenantInfo.gender.getValue(),
                    firstname: tenantInfo.firstname.getValue(),
                    lastname: tenantInfo.lastname.getValue(),
                    address: addr,
                    birthdate: moment(tenantInfo.dateOfBirth.getValue()).format('YYYY-MM-DD'),
                    phone: tenantInfo.phoneNumber.getValue(),
                    phone_secondary: tenantInfo.phoneSecondary.getValue(),
                    email: tenantInfo.email.getValue(),
                    comments: tenantInfo.comment.getValue(),
                    language: tenantsLang,
                },
                sub_tenants: subTenantsInfo.map((t) => {
                    let date = moment(t.dateOfBirth.getValue(0)).format('YYYY-MM-DD');
                    if (date == 'Invalid date') {
                        date = null;
                    }
                    return {
                        gender: t.gender.getValue(),
                        firstname: t.firstname.getValue(),
                        lastname: t.lastname.getValue(),
                        birthdate: date,
                    };
                })
            };

            Swal.showLoading()
            axios.post(`${BASE_URL}/properties/${this.stepper.propertyId}/bookings/create`, body)
            .then((res) => {
                console.debug(res.data);
                resolve(res.data);
            })
            .catch((err) => {
                console.debug(err);
                reject(err);
            })
            .finally(() => {
                Swal.hideLoading();
            });
        });
    }
}
