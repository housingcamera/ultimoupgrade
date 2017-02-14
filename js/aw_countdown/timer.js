var AwCountdown = Class.create({
    initialize: function (format, time, container) {
        this.dateTo = time;
        this.container = container;
        this.format = format;
        this.getCounter();
    },
    addZero: function (str) {
        if (str < 10) {
            return '0' + str;
        }
        return str;
    },
    getCounter: function () {
        amount = this.dateTo;
        if (amount < 0) {
            window.location.href = controllerUrl;
            return;
        }
        days = Math.floor(amount / 86400);
        amount = amount % 86400;
        hours = Math.floor(amount / 3600);
        amount = amount % 3600;
        mins = Math.floor(amount / 60);
        amount = amount % 60;
        secs = Math.floor(amount);
        tmp = $(this.container).getElementsByClassName('aw_countdown_timer');
        timerContainer = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_days_container');
        daysContainer = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_hours_container');
        hoursContainer = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_separator_afterhours');
        afterhoursSeparator = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_minutes_container');
        minsContainer = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_separator_afterminutes');
        afterminsSeparator = tmp[0];
        tmp = timerContainer.getElementsByClassName('aw_countdown_seconds_container');
        secsContainer = tmp[0];
        daysContainer.down(2).update(this.addZero(days));
        if (this.format.indexOf('H') != -1) {
            hoursContainer.down(2).update(this.addZero(hours));
        } else {
            hoursContainer.style.display = 'none';
        }
        if (this.format.indexOf('M') != -1) {
            minsContainer.down(2).update(this.addZero(mins));
        } else {
            minsContainer.style.display = 'none';
            afterhoursSeparator.style.display = 'none';
        }
        if (this.format.indexOf('S') != -1) {
            secsContainer.down(2).update(this.addZero(secs));
        } else {
            secsContainer.style.display = 'none';
            afterminsSeparator.style.display = 'none';
        }
        this.dateTo = this.dateTo - 1;
        setTimeout(function () {
            this.getCounter();
        }.bind(this), 1000);
    }
});
 