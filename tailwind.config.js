module.exports = {
  content: [
    './*.php',
    './**/*.php',
    './src/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        black: "#000000",
        white: {
          100: "#FFFFFF",
          99: "#FFFBFF"
        },
        brown: {
          800: "#291808",
          750: "#412C1B",
          700: "#59422F",
          650: "#735945",
          600: "#8D725C",
          550: "#A98B74",
          500: "#C5A58D",
          450: "#E1C0A7",
          400: "#FFDCC2",
          350: "#FFEDE2"
        },
        red: {
          800: "#410002",
          750: "#690005",
          700: "#93000A",
          650: "#BA1A1A",
          600: "#DE3730",
          550: "#FF5449",
          500: "#FF897D",
          450: "#FFB4AB",
          400: "#FFDAD6",
          350: "#FFEDEA"
        },
        grey: {
          800: "#1D1B1A",
          750: "#32302E",
          700: "#494645",
          650: "#615E5C",
          600: "#7A7674",
          550: "#94908E",
          500: "#AFAAA8",
          450: "#CBC5C3",
          400: "#E7E1DF",
          350: "#F6EFED"
        },
        'neutral-variant': {
          800: "#1E1B18",
          750: "#34302D",
          700: "#4B4643",
          650: "#635D5A",
          600: "#7C7672",
          550: "#968F8C",
          500: "#B1AAA6",
          450: "#CDC5C1",
          400: "#E9E1DD",
          350: "#F8EFEB"
        },
        light: {
          p40: "#88511D",
          p10: "#2E1500",
          p80: "#FFB77B",
          s30: "#6B3A05",
          n87: "#E7D7CD",
          n98: "#FFF8F5",
          n96: "#FFF1E8",
          n94: "#FBEBE1",
          n92: "#F5E5DB",
          n90: "#EFE0D6",
          n10: "#221A14",
          nv30: "#51443B",
          nv50: "#847469",
          nv80: "#D6C3B6",
          n20: "#382F28",
          n95: "#FEEEE4"
        },
        dark: {
          p80: "#FFBD86",
          p20: "#4C2700",
          p30: "#6B3A05",
          p90: "#FFDCC2",
          p10: "#2E1500",
          e80: "#690005",
          s30: "#6B3A05",
          n6: "#19120C",
          n24: "#413731",
          n4: "#140D08",
          n10: "#221A14",
          n12: "#261E18",
          n17: "#312822",
          n22: "#3C332C",
          n90: "#EFE0D6",
          nv80: "#D6C3B6",
          nv60: "#9E8E82",
          nv30: "#51443B",
          n20: "#382F28",
          p40: "#88511D"
        },
        blue: {
          800: "#001D33",
          750: "#003353",
          700: "#0E4A73",
          650: "#2F628C",
          600: "#4A7BA7",
          550: "#6595C2",
          500: "#80B0DE",
          450: "#9BCBFB",
          400: "#CEE5FF",
          350: "#E8F2FF"
        },
        green: {
          800: "#062100",
          750: "#163808",
          700: "#2C4F1D",
          650: "#436833",
          600: "#5B8149",
          550: "#749B60",
          500: "#8EB679",
          450: "#A8D292",
          400: "#C4EFAC",
          350: "#D2FDB9"
        }
      },
      fontFamily: {
        sans: ['Roboto', 'sans-serif'],
      },
      
      fontWeight: {
        medium: 500,
        regular: 400,
        bold: 700,
      },
      lineHeight: {
        none: 1,
        tight: 1.25,
        snug: 1.375,
        normal: 1.5,
        relaxed: 1.625,
        loose: 2,
      },
      letterSpacing: {
        tighter: '-0.05em',
        tight: '-0.025em',
        normal: '0em',
        wide: '0.025em',
        wider: '0.05em',
        widest: '0.1em',
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
};
