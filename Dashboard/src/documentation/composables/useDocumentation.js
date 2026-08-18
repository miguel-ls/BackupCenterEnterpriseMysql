import navigation from '../constants/navigation';

export function useDocumentation(route) {

    const currentIndex = navigation.findIndex(
        item => item.path === route.path
    );

    return {

        navigation,

        current: navigation[currentIndex] ?? null,

        previous: currentIndex > 0
            ? navigation[currentIndex - 1]
            : null,

        next: currentIndex < navigation.length - 1
            ? navigation[currentIndex + 1]
            : null

    };

}