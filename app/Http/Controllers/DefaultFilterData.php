<?php

namespace App\Http\Controllers;

use App\Exports\VacanciesExport;
use App\Models\Vacancies;
use App\Models\Websites;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DefaultFilterData
{

    public static function job_categories() {
        $cat['Administration, Controlling & Finanzen'] = [
                'Buchhaltung & Finanzen' => [
                    'Finance',
                    'Accounting & Finance',
                    'Controlling',
                    'Cost Engineering',
                    'Finance & Accounting',
                    'Finance / Controlling',
                    'Finance and Controlling',
                    'Finance and Controlling',
                    'Finance and Controlling, General Management',
                    'Finance and Controlling, Law Patents and Licences',
                    'Finance and Controlling, Marketing and Sales',
                    'Finance and Controlling, Project Management',
                    'Finance, Accounting & Legal',
                    'Finance, Analytics & Controlling',
                    'Finanz & Rechnungswesen',
                    'Finanzdienstleistungen',
                    'Finanzen',
                    'Finanzen & Controlling',
                    'Finanzen & Rechnungswesen',
                    'Finanzen / Controlling',
                    'Finanzen und Controlling',
                    'Finanzen/Controlling',
                    'FinanzgeschÃ¤fte',
                    'Finanzplanung',
                    'Rechnungswesen',
                ],
                'Administration & Verwaltung' => [
                    'Administration',
                    'Administration & Office Support',
                    'Administration & Services',
                    'Administration / Verwaltung',
                    'Administration and Assistance',
                    'Administration and Assistance, Engineering, Manufacturing Operations and Production',
                    'Administration and Assistance, General Management',
                    'Administration and Assistance, Information Technology, Project Management',
                    'Administration Finanzdienstleistung',
                    'Administrative',
                    'Administrative Assistance',
                    'Administrative Support',
                    'Central Services',
                    'KaufmÃ¤nnischer Bereich',
                    'Organisation & allgemeine Verwaltung',
                    'Verwaltung',
                ],
             ];
        $cat['Einkauf, Legal & Compliance'] = [
                'Legal & Compliance' => [
                    'Legal & Compliance',
                    'Audit',
                    'Auditing',
                    'Auditing, Information Technology',
                    'Auditing, Information Technology, Manufacturing Operations and Production, Marketing and Sales, Quality, Research and Development',
                    'Compliance',
                    'Datenschutz',
                    'Handels- und Gesellschaftsrecht',
                    'Handelscompliance',
                    'Homologation',
                    'IntegritÃ¤t und Recht',
                    'Internal Audit',
                    'Interne Revision',
                    'Law Patents and Licences',
                    'Law Patents and Licences, Project Management',
                    'Legal',
                    'Legal & Compliance',
                    'Legal & Tax',
                    'Legal / SEA',
                    'Legal, Compliance & Governance',
                    'Patentrecht',
                    'Politik',
                    'Preispolitik',
                    'Recht',
                    'Recht & Compliance',
                    'Recht & Steuern',
                    'Revision',
                    'Steuern',
                    'Transport und Logistik',
                    'Underwriting',
                    'WirtschaftsprÃ¼fung, Steuern und Recht',
                ],
                'Einkauf'=> [
                    'Aankoop / Inkoop',
                    'Beschaffung',
                    'Buying & Merchandising',
                    'Direkter Einkauf',
                    'Einkauf',
                    'Einkauf & Logistik',
                    'Indirekter Einkauf',
                    'Procurement',
                    'Purchasing',
                    'Purchasing, Quality',
                    'Purchasing, Research and Development',
                    'Strategie Einkauf',
                ],
                'Logistik' => [
                    'Logistics & Supply Chain',
                    'Logistics & Supply Chain Management',
                    'Logistics, Manufacturing Operations and Production',
                    'Logistics, Project Management',
                    'Logistics, Purchasing',
                    'Logistics, Quality',
                    'Logistik',
                    'Logistik & Supply Chain Management',
                    'Logistikplanung',
                    'Produktions-/Supply Chain Management',
                    'Supply Chain',
                    'Supply Chain / Planning / Customer Care',
                    'Supply Chain Management',
                    'Supply Chain Management & Operations',
                ]
            ];
        $cat['Environment, Health & Safety'] = [
            'Umwelt' => [
                'Environmental Protection, Health & Safety',
                'Energie, Wasser und Umwelt',
                'Energiemanagement',
                'Energiewirtschaft',
                'Environment, Health & Safety',
                'Environment, Health Safety and Security',
                'Environment, Health Safety and Security, Human Resources',
                'Environment, Human Resources',
                'Nachhaltigkeit',
                'Nachhaltigkeit und Umwelt',
                'Umwelt- und Energiemanagement',
                'Umwelt, Gesundheit & Sicherheit',
                'Umweltmanagement',
            ],
            'Health' => [
                'Arbeitsmedizin',
                'Arbeitssicherheit',
                'Gesundheit und Soziales',
                'Gesundheitsmanagement',
                'Gesundheitsschutz und Sicherheit',
                'Health Safety and Security',
                'Health Safety and Security, Human Resources',
                'Health Safety and Security, Research and Development',
            ],
            'Sicherheit' => [
                'Konzernsicherheit'
            ],
        ];
        $cat['Fertigung'] = [
            'Fertigung' => [
                'Manufacturing',
                'Fertigung',
                'Fertigung, Bau, Handwerk',
                'Handwerk',
                'Industrie und Maschinenbau',
                'Manufacturing',
                'Manufacturing / Sample Manufacturing',
                'Manufacturing Operations and Production',
                'Maschinenbau und Betriebstechnik',
                'Material- und Teileversorgung',
                'Metallindustrie und -verarbeitung',
                'Montage',
                'Presswerk',
                'Production',
                'Production / Manufacturing',
                'Production jobs',
                'Produktion',
                'Produktion und Logistik',
                'Produktionsplanung',
                'Produktionstechnik',
                'Werkstatt',
                'Werkzeugbau'
            ],
            'Fahrzeugbau' => [
                'Antrieb',
                'Automobil und Fahrzeugbau',
                'ElektromobilitÃ¤t',
                'E-Mobility',
                'Fahrerassistenzsysteme',
                'Fahrerlebnisplatz',
                'Fahrwerk / Fahrdynamik',
                'Fahrzeugkarosserie',
                'Fahrzeuglogistik',
                'Fahrzeugsicherheit',
                'Gesamtfahrzeug',
                'Karosserie',
                'Lackiererei',
                'Rohkarosserie',
                'Smart Mobility'
            ],
        ];
        $cat['Handel & Logistik'] = [
            'Logistik & Supply Chain' => [
                'SCM-Procurement / Supply Chain Logistics',
                'FlottengeschÃ¤ft',
                'Lagerhaltung & Kommissionierung',
                'Logist\'ics',
                'Physische Logistik',
                'Planung / Organisation',
            ],
            'handel' => [
                'KonsumgÃ¼ter und Handel',
                'Lebensmittel',
                'Lieferantennetzwerk',
                'Lieferantenrisiko',
            ],
        ];
        $cat['HR'] = [
            'Personalwesen' => [
                'Human Resources',
                'People & Organization',
                'Human Resources',
                'Human Resources, Law Patents and Licences',
                'Human Resources, Manufacturing Operations and Production',
                'Human Resources, Marketing and Sales',
                'Human Resources, Project Management',
                'People',
                'People Operations',
                'People, Learning & Support',
                'Personal',
                'Personalmanagement',
                'Personalwesen',
                'Personeelszaken / Human Resources',
                'Recursos Humanos',
                'Ressources humaines',
                'Work placement'
            ],
            'Weiterbildung' => [
                'Aus- und Weiterbildung',
                'Education and Training',
                'Erziehung, Bildung und Wissenschaft',
                'Training'
            ],
            'Personaladministration' => [
                'Gehaltsabrechnung',
                'Personaladministration',
                'Personaldienstleistungen und -beratung'
            ],
            'Personalbeschaffung' => [
                'Personalstrategie und -planung',
                'Recruiting',
                'Sourcing'
            ],
            'Pfelgeberufe' => ['Pflegeberufe'],
        ];
        $cat['IT, Daten & Technik'] = [
            'IT' => [
                'Industrial Engineering',
                'Information Technology',
                'Information Technology, Logistics',
                'Information Technology, Marketing and Sales',
                'Information Technology, Project Management',
                'Information Technology, Project Management, Research and Development',
                'Information Technology, Research and Development',
                'IT',
                'IT & Digitalisierung',
                'IT & Entwicklung',
                'IT & Tech Engineering',
                'IT / Digitale Transformation',
                'IT / Information Management',
                'IT / Informationsmanagement',
                'IT / Software Development',
                'IT Administration',
                'IT Architektur',
                'IT Bereitstellung',
                'IT Betrieb',
                'IT Consulting & Operations',
                'IT Design',
                'IT Infrastruktur',
                'IT Innovationen und Forschung',
                'IT Projektmanagement',
                'IT Security',
                'IT Strategie',
                'Software',
            ],
            'Technik' => [
                'Analog-/Mixed-Signal Design',
                'Anlagenplanung',
                'Digital',
                'Elektrik / Elektronik',
                'Field Service / Technical Support',
                'Internet und Informationstechnologie',
                'Neue Technologien',
                'Produktion-Backend',
                'Produktion-Frontend',
                'Tech',
                'Technik',
                'Technik und Produktion',
                'Technischer Bereich',
                'Technisches Labor',
            ],
            'Softwareentwicklung' => [
                'Application Engineering',
                'Software Engineering',
                'Software Engineering - Architecture',
                'Software Engineering - Backend',
                'Software Engineering - Data',
                'Software Engineering - Frontend',
                'Software Engineering - Full Stack',
                'Software Engineering - Leadership',
                'Software Engineering - Machine Learning',
                'Software Engineering - Mobile',
                'Software Engineering - Principal Engineering',
                'Software-Design and Development',
                'Software-Development Operations',
                'Softwareentwicklung',
                'Softwareentwicklung Automotive',
                'Software-Quality Assurance',
                'Software-Research',
                'Software-User Experience',
                'Technologieentwicklung'
            ],
            'Data & Analytics' => [
                'Business Intelligence',
                'Data',
                'Data & Analytics',
                'Data Analytics & Digital Business',
                'Data Science',
                'Data Science / Analytics',
            ],
        ];
        $cat['Management & Unternehmensführung'] = [
            'Unternehmensführung' => [
                'General Management',
                'Allg. Management / UnternehmensfÃ¼hrung',
                'General Management',
                'General Management, Insurance',
            ],
            'Strategie' => [
                'Strategy',
                'GeschÃ¤ftsentwicklung',
                'Marktrisiko',
                'Strategic Development',
                'Strategie',
                'Strategie & Management',
                'Strategie / GeschÃ¤ftsleitung',
                'Strategie / Projektmanagement',
                'Strategie Produktionsnetzwerk',
                'Strategische Planung',
                'Strategy',
                'Strategy & Business Development',
                'Strategy and Investor Relations',
            ],
            'Management' => [
                'FÃ¼hrungsposition',
                'Global Management',
                'Global Operations',
            ],
        ];
        $cat['Marketing & Kommunikation'] = [
            'Kommunikation' => [
                'Communications',
                'Communication',
                'Communication & Public Relations',
                'Communications',
                'Communications / PR / IR',
                'Communications, Engineering, Marketing and Sales',
                'Communications, Engineering, Project Management',
                'Communications, Environment, Health Safety and Security',
                'Communications, Human Resources',
                'Communications, Human Resources, Marketing and Sales',
                'Communications, Human Resources, Project Management',
                'Communications, Key Account Management, Marketing and Sales',
                'Communications, Marketing and Sales',
                'Communications, Marketing and Sales, Project Management',
                'Communications, Project Management',
                'Communications, Project Management, Quality',
                'Communications, Quality',
                'Communications, Research and Development',
                'Corporate Communications / PR',
                'Corporate Operations',
                'Externe Unternehmenskommunikation',
                'Interne Unternehmenskommunikation',
                'Kommunikation',
                'Kommunikation & Ã–ffentlichkeitsarbeit',
                'Kommunikation/MARCOM',
                'Markenkommunikation',
                'Markenmanagement',
                'PR / Kommunikation',
                'Public Relations / Communications',
                'Regulatory Affairs'
            ],
            'Marketing' => [
                'Marketing',
                'Commercial',
                'Digitales Marketing',
                'E-Commerce',
                'e-Commerce / Digital design',
                'Events',
                'Handelsmarketing',
                'Marketing',
                'Marketing & Communications',
                'Marketing & Design',
                'Marketing / Product Management',
                'Marketing / Unternehmenskommunikation',
                'Marketing and Sales',
                'Marketing and Sales, Project Management',
                'Marketing und Werbung',
                'Marktforschung und Wettbewerb',
                'Merchandising and Go-To-Market',
                'Produktmarketing'
            ],
        ];
        $cat['Operations'] = [
            'Versicherung' => [
                'Assurance',
                'Actuarial',
                'Versicherung & Risikomanagement',
                'Versicherungen',
                'Versicherungsmathematisch',
            ],
            'Immobilien' => [
                'Real Estate',
                'Architektur und Bauwesen',
                'Asset & Investment Management',
                'Baugewerbe',
                'Bauingenieurwesen',
                'Baumanagement / Immobilien / Architektur',
                'Corporate Real Estate',
                'Garten- &amp; Landschaftsbau',
                'GebÃ¤ude-/Betriebsmanagement',
                'GebÃ¤udemanagement',
                'Immobilien',
                'Immobilien- & Facility-Management',
                'Real Estate',
            ],
            'Investment Management & Banking' => [
                'Banken und Finanzdienstleistungen',
                'Banking',
                'Kreditrisiko',
                'Mergers and Acquisitions',
                'Restrisiko',
                'Risiko Management',
                'Risk Management',
                'Treasury Risk',
                'VermÃ¶gens- & Anlageverwaltung'
            ],
            'Operations' => [
                'Beratung',
                'Beratung und Consulting',
                'Beratung, Datenanalyse & Projektmanagement',
                'Betrieb',
                'Business Solutions',
                'Consulting',
                'Consulting  and Professional Services',
                'GieÃŸerei',
                'Instandhaltung',
                'Instandhaltung / AnlagenfÃ¼hrung',
                'Instandsetzung / Reparatur',
                'Operations',
                'Prozess-/Verfahrenstechnik',
                'Prozessoptimierung',
                'Rohstoffmanagement',
                'Zustellung'
            ],
            'Hotel & Gastro' => [
                'Betriebsgastronomie',
                'Gastronomie',
                'Gastronomie / Service',
                'Hotel & Gastronomie',
                'Hotelgewerbe',
                'Tourismus und Gastronomie'
            ],
            'Medizin & Pharma' => [
                'Clinical / Medical Affairs',
                'Kosmetik und KÃ¶rperpflege',
                'KrankenhÃ¤user',
                'Medical Affairs',
                'Medio Ambiente, Higiene y Seguridad',
                'Medizin / Soziales',
                'Pharma und Medizintechnik'
            ],
        ];
        $cat['Others'] = [
            'Others' => [
                'Additional Jobs',
                'CFK',
                'Feuerwehr',
                'No Functional Area',
                'Not Applicable (N/A)',
                'Other',
                'Others',
                'Performance Center',
                'Sonstige',
                'Sonstige Branchen',
                'Sonstige Dienstleistungen',
                'Vocational Training',
                'Weitere Bereiche',
                'Weitere Jobs',
                'Wirtschaft und Service',
            ]
        ];
        $cat['Presales, Sales & Aftersales'] = [
            'Kundenservice' => [
                'Customer Services',
                'Customer Care',
                'Customer Service',
                'Customer Service and Support',
                'Customer Services & Claims',
                'Kundenservice',
                'Kundenservice & Reklamation',
            ],
            'Verkauf & Vertrieb' => [
                'Sales',
                'Business Relationship Management',
                'Einzelhandel',
                'GroÃŸhandel',
                'HÃ¤ndlerbetreuung',
                'Retail',
                'Retail (Back Office)',
                'Retail (Store)',
                'Retail Corporate',
                'Sales',
                'Sales & Account Management',
                'Sales & Distribution',
                'Sales / Business Development',
                'Sales / Customer Support',
                'Sales Operations',
                'Sales Support',
                'Verkauf',
                'Verkauf & Vertrieb',
                'Verkaufsstrategie',
                'Vertrieb',
                'Vertrieb & Marketing',
                'Vertrieb & Unternehmensentwicklung',
                'Vertrieb / Verkauf / Retail',
                'Vertrieb GroÃŸkunden',
                'Vertrieb und Marketing',
                'VertriebsaktivitÃ¤ten',
                'Vertriebswegeentwicklung',
            ],
            'Aftersales' => [
                'After Sales',
                'Aftersales Kundenzufriedenheit',
                'Aftersales Marketing',
                'Aftersales Preis- und Volumenplanung',
                'Aftersales Service und Garantie',
                'Aftersales Strategie und Prozesse',
                'Aftersales Teilelogistik',
                'Aftersales Vertrieb',
                'Aftersales VertriebskanÃ¤le',
                'Regionales Aftersales Management',
            ],
            'Presales' => [
                'Presales'
            ],
        ];
        $cat['Qualitäts- & Projektmanagement, andere interne Funktionen'] = [
            'Interne Funktion' => [
                'Internal Services',
                'Beratung / Organisation',
                'Changemanagement',
                'Dienstleistungen',
                'Facilities & Services',
                'Facility Management',
                'Inhouse Consulting',
                'Reinigung'
            ],
            'Research & Development' => [
                'Product Management, Portfolio & Innovation',
                'Research & Development'
            ],
            'Projektmanagement' => [
                'Project Management',
                'Anlauf- und Ã„nderungsmanagement',
                'Key Account Management',
                'Key Account Management, Marketing and Sales',
                'Key Account Management, Project Management',
                'Programm-Management',
                'Project Management',
                'Project Management & Planning',
                'Projektmanagement',
                'Projektmanagement / PMO',
            ],
            'Qualitäts Management' => [
                'Quality Management',
                'Kontinuierliche Verbesserung',
                'Management LieferantenqualitÃ¤t',
                'QA / QC / Regulatory Affairs',
                'QualitÃ¤t Gesamtfahrzeug',
                'QualitÃ¤tskontrolle - und sicherung',
                'QualitÃ¤tsmanagement',
                'QualitÃ¤tsmanagementsysteme',
                'QualitÃ¤tssicherung',
                'QualitÃ¤tssteuerung und - management',
                'Quality',
                'Quality Control & Management',
                'Quality Engineering',
                'Quality Management',
                'TeilequalitÃ¤t'
            ],
            'Investment Management' => [
                'Asset / Lease Management',
            ],
        ];
        $cat['Research & Development'] = [
            'Engineering' => [
                'Engineering',
                'Engineering',
                'Engineering & Skilled Trade',
                'Engineering, Facility Management',
                'Engineering, General Management, Project Management, Research and Development',
                'Engineering, Industrial Engineering',
                'Engineering, Industrial Engineering, Information Technology',
                'Engineering, Industrial Engineering, Manufacturing Operations and Production',
                'Engineering, Industrial Engineering, Manufacturing Operations and Production, Research and Development',
                'Engineering, Industrial Engineering, Research and Development',
                'Engineering, Information Technology',
                'Engineering, Information Technology, Project Management',
                'Engineering, Information Technology, Research and Development',
                'Engineering, Key Account Management, Research and Development',
                'Engineering, Manufacturing Operations and Production',
                'Engineering, Manufacturing Operations and Production, Project Management, Research and Development',
                'Engineering, Manufacturing Operations and Production, Trainee Initiatives',
                'Engineering, Marketing and Sales',
                'Engineering, Project Management',
                'Engineering, Project Management, Quality',
                'Engineering, Project Management, Research and Development',
                'Engineering, Purchasing',
                'Engineering, Quality',
                'Engineering, Quality, Research and Development',
                'Engineering, Research and Development',
            ],
            'Forschung' => [
                'Applied Science',
                'Forschung & Entwicklung',
                'Forschung &amp; Entwicklung',
                'Forschung / Vorentwicklung',
                'Forschung und Entwicklung',
            ],
            'Entwicklung' => [
                'Autonomes Fahren',
                'Batterieentwicklung',
                'Connected Car',
                'Entwicklung',
                'Entwicklung - Antrieb / Motor',
                'Entwicklung - Elektrik / Elektronik',
                'Entwicklung - Fahrwerk',
                'Entwicklung - Gesamtfahrzeug',
                'Entwicklung - Karosserie / Exterieur und Interieur',
                'Entwicklung - Motorsport',
                'Entwicklung - Produkt und Konzept',
                'Entwicklung - Technische KonformitÃ¤t',
                'Entwicklung - Vorentwicklung / Strategie',
                'Entwicklung - WerkstÃ¤tten',
                'Entwicklung â€“ PrÃ¼ffeld',
                'Entwicklung individuelle Kundenanforderungen',
                'Fashion Product Development & Design',
                'Maintenance / Engineering / Facility Management',
                'Reinsurance',
                'Research & Development',
                'Research and Development',
                'Testentwicklung',
                'Testumgebung und Erprobung',
                'Toxicology / Product Safety',
                'Toxikologie / Produktsicherheit',
                'Wirtschaftsingenieurwesen',
            ],
            'Design' => [
                'Creative & Design',
                'Creative Production',
                'Design',
                'Design / Gestaltung',
                'Digital Design',
            ],
            'Produktmanagement' => [
                'Product',
                'Product Application',
                'Product Design, User Research & UX Writing',
                'Product Development',
                'Product Management',
                'Product Management - Tech',
                'Product Management (Technology)',
                'Produkt Anlauf- und Ã„nderungsmanagement',
                'Produkt-/Systemarchitektur',
                'Produktentwicklung',
                'Produkterprobung und -analyse',
                'Produktionsentwicklung & Support',
                'Produktmanagement',
                'Produktplanung und -steuerung',
                'Produktstrategie',
                'Produktverifikation',
                'Solution and Product Management'
            ],
        ];

        return $cat;
    }

    public static function job_levels() {
        $level = [
            'Professionals' => [
                'Professionals',
                'Professional',
                'Berufserfahren',
                'Berufserfahrene',
                'Graduates,Professional Experienced',
            ],
            'Studentenjobs' => [
                'Student/in',
                'Student',
                'Studenten',
                'Students'
            ],
            'Ausbildung/Duales Studium' => [
                'Apprenticeship / Dual Studies',
                'Ausbildung/Duales Studium',
            ],
            'Management' => [
                'Management',
                'Executives',
                'Manager',
            ],
            'Berufseinsteiger' => [
                'Einsteigsstufe',
                'Entry Level',
                'Berufseinsteiger',
                'Absolventen',
            ]
        ];

        return $level;
    }
}
