namespace Forms {
    export type Response = {
        status: 1 | 0;
        text: string
    }

    export type Errors = {
        [key: string]: string;
    }
}
