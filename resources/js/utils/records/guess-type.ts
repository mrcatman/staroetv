export const guessType = (record: Models.Record): Records.Type => {
    if (record.is_interprogram) {
        return record.program_id ? 'program-design' : 'interprogram';
    }
    if (record.is_advertising) {
        return 'advertising';
    }
    if (record.is_clip) {
        return 'clips';
    }
    if (!record.channel_id && !record.program_id && !record.is_advertising) {
        return 'other';
    }
    return 'programs';
}
