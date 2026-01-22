namespace Forms {
    export type Response = {
        status: 1 | 0;
        text: string
    }

    export type Errors = {
        [key: string]: string;
    }

    export type PaginatedResponse<T> = {
        current_page: number;
        data: T[];
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        links: any[];
        next_page_url: string;
        path: string;
        per_page: number;
        prev_page_url: string;
        to: number;
        total: number;
    }
}
