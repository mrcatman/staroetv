export const getErrorMessage = (error: any, defaultText: string = 'Неизвестная ошибка, повторите позже') => {
    const errorObj = error?.responseJSON || error;
    if (errorObj) {
        return errorObj.message ?? errorObj.exception ?? errorObj.text ?? defaultText;
    }
    return defaultText;

}
