import { Node, mergeAttributes } from '@tiptap/core'

export const VideoEmbed = Node.create({
    name: 'video-embed',
    group: 'block',
    selectable: true,
    draggable: true,
    atom: true,

    addAttributes() {
        return {
            "src": {
                default: null
            },
        }
    },

    parseHTML() {
        return [
            {
                tag: 'iframe',
            },
        ]
    },

    renderHTML({ HTMLAttributes }) {
        return ['iframe', mergeAttributes(HTMLAttributes)];
    },

    addNodeView() {
        return ({ editor, node }) => {
            const iframe = document.createElement('iframe');

            iframe.frameBorder = "0";
            iframe.allowFullscreen = true;
            iframe.src = node.attrs.src;
            return {
                dom: iframe,
            }
        }
    },
});

export const parseVideoUrl = (url: string): string | null => {
    if (!url) return null;

    if (url.includes('iframe')) {
        const match = url.match(/src="([^"]+)"/);
        if (match) {
            return match[1];
        }
    }

    const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/);
    if (youtubeMatch) {
        return `https://www.youtube.com/embed/${youtubeMatch[1]}`;
    }

    return url;
}
