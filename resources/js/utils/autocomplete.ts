import {type Options, QueryOptions} from "select2";

export const autocompleteOptions = (
    initialValue: string,
    ajaxUrl: string | ((params: QueryOptions) => string),
): Options => {
    return {
        data: [
            {
                id: initialValue,
                text: initialValue
            }
        ],
        tags: true,
        createTag: (params) => {
            return {
                id: params.term,
                text: params.term,
                newOption: true
            }
        },
        ajax: {
            method: 'GET',
            url: ajaxUrl,
            dataType: 'json',
            processResults: ({data}) => {
                return {
                    results: data.filter(item => item?.name.length).map((item: string) => {
                        return {
                            id: item.name,
                            text: item.name,
                        }
                    }),
                    pagination: {
                        more: data.length > 0
                    }
                }
            }
        }
    }
}
