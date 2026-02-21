declare module 'page-flip' {
    type PageFlipEvent = {
        data: unknown;
        object: PageFlip;
    };

    export class PageFlip {
        constructor(rootElement: HTMLElement, settings: Record<string, unknown>);
        on(eventName: string, callback: (event: PageFlipEvent) => void): this;
        off(eventName: string): void;
        loadFromHTML(items: NodeListOf<HTMLElement> | HTMLElement[]): void;
        updateFromHtml(items: NodeListOf<HTMLElement> | HTMLElement[]): void;
        getCurrentPageIndex(): number;
        getPageCount(): number;
        turnToPage(pageNumber: number): void;
        flipNext(corner?: 'top' | 'bottom'): void;
        flipPrev(corner?: 'top' | 'bottom'): void;
        update(): void;
        destroy(): void;
    }
}
