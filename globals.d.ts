interface OzopanelI18n {
	[key: string]: string;
}

interface OzopanelConfig {
	i18n: OzopanelI18n;
}

declare const ozopanel: Readonly<OzopanelConfig>;
declare const wp: Readonly; //This is by default wordpress object
declare const gate: Readonly;
