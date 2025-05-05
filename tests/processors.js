module.exports = {
    beforeRequest: (requestParams, context, ee, next) => {
        requestParams.headers['Authorization'] = `Bearer ${process.env.API_TOKEN}`;
        return next();
    }
};
