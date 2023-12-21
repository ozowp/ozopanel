interface OzopanelI18n {
    [key: string]: string;
}

interface OzopanelConfig {
    version: string;
    dashboard: string;
    assetImgUri: string;
    assetUri: string;
    i18n: OzopanelI18n;
}

declare const ozopanel: Readonly<OzopanelConfig>;
declare const wp: Readonly; //This is by default wordpress object
declare const gate: Readonly;