import dayjs from 'dayjs';

export default {
    methods: {
        formatDate(dateString) {
            const date = dayjs(dateString);
            // Then specify how you want your dates to be formatted
            return date.format('MMMM D, YYYY h:mm A');
        }
    }
}
