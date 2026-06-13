export default function translit(str: string) {
    let ru = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
        'е': 'e', 'ё': 'e', 'ж': 'j', 'з': 'z', 'и': 'i',
        'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
        'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh',
        'щ': 'shch', 'ы': 'y', 'э': 'e', 'ю': 'u', 'я': 'ya'
    }, result: string[] = [];
    str = str.toLocaleLowerCase().replace(/[^a-zA-Z0-9а-яА-ЯёЁ\s]/gu, '').replace(/\s+/g, ' ').trim().split(' ').join('-');
    str = str.replace(/[ъь]+/g, '').replace(/й/g, 'i');

    for ( let i = 0; i < str.length; ++i ) {
        result.push(
            ru[ str[i] ] ?? str[i]
        );
    }

    return result.join('');
}
